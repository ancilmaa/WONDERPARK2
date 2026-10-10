<?php

namespace App\Traits;

use App\Services\SystemLogger;
use Illuminate\Support\Str;
use Throwable;

/**
 * Idagdag sa kahit anong Eloquent model para ma-log ang create / update / delete.
 *
 *   class InventoryItem extends Model
 *   {
 *       use LogsActivity;
 *       protected string $logModule = 'inventory';   // optional, kung wala: pangalan ng class
 *       protected string $logLabel  = 'name';        // optional, field na ipapakita sa description
 *   }
 *
 * Tandaan: ang mass update/delete gamit ang query builder (Model::where(...)->delete())
 * ay HINDI nagti-trigger ng model events, kaya hindi ito mala-log ng trait.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn ($model) => $model->writeActivityLog('created'));
        static::updated(fn ($model) => $model->writeActivityLog('updated'));
        static::deleted(fn ($model) => $model->writeActivityLog('deleted'));
    }

    protected function activityModule(): string
    {
        return property_exists($this, 'logModule') ? $this->logModule : Str::snake(class_basename($this));
    }

    protected function activityLabel(): string
    {
        $field = property_exists($this, 'logLabel') ? $this->logLabel : null;
        $label = $field ? $this->getAttribute($field) : null;

        return $label ? "#{$this->getKey()} ({$label})" : "#{$this->getKey()}";
    }

    protected function writeActivityLog(string $action): void
    {
        // Huwag mag-log habang seeding / migration / artisan commands
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        try {
            $module = $this->activityModule();
            $label = $this->activityLabel();
            $hidden = (array) config('system_log.hidden_fields', []);
            $context = [];

            if ($action === 'updated') {
                $changes = [];
                foreach ($this->getChanges() as $field => $new) {
                    if (in_array($field, $hidden, true)) {
                        continue;
                    }
                    $old = $this->getOriginal($field);
                    $changes[$field] = [
                        'from' => is_string($old) ? Str::limit($old, 100) : $old,
                        'to' => is_string($new) ? Str::limit($new, 100) : $new,
                    ];
                }

                if (! $changes) {
                    return; // walang makabuluhang nagbago
                }
                $context['changes'] = $changes;
            }

            $verb = ['created' => 'Nagdagdag ng', 'updated' => 'Binago ang', 'deleted' => 'Nag-delete ng'][$action];
            $severity = $action === 'deleted' ? 'warning' : 'info';

            SystemLogger::record(
                "{$module}_{$action}",
                "{$verb} {$module} {$label}.",
                $severity,
                $context,
                false,
                null,
                null,
                $module
            );

            if ($action === 'deleted') {
                SystemLogger::checkBulkDelete(auth()->id(), $module);
            }
        } catch (Throwable $e) {
            // Hindi dapat masira ang app dahil lang sa logging
            report($e);
        }
    }
}
