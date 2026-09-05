<?php

namespace App\Http\Controllers;

use App\Models\SiteCard;
use App\Models\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CmsController extends Controller
{
    public function __construct()
    {
        // Direktang check, hindi na dumadaan sa $this->middleware() —
        // wala na kasing HasMiddleware/middleware() sa base Controller
        // simula Laravel 11. Sunod sa parehong session-based role check
        // na ginagamit sa sidebar (session('role') === 'admin').
        abort_unless(session('role') === 'admin', 403);
    }

    /**
     * Field definitions for the simple text sections.
     *
     * type:
     *   text      -> plain <input type="text">, stored as plain text
     *   textarea  -> plain <textarea>, stored as plain text
     *   richtext  -> Word-like editor (bold/italic/underline/font size),
     *                stored as sanitized HTML
     *   video     -> file upload (mp4/webm/mov), stored as a public URL
     */
    private array $sectionFields = [
        'hero' => [
            'title'        => ['label' => 'Headline', 'type' => 'richtext'],
            'description'  => ['label' => 'Description', 'type' => 'textarea'],
            'price_teaser' => ['label' => 'Price Teaser (e.g. ₱149)', 'type' => 'text'],
            'video_path'   => ['label' => 'Background Video', 'type' => 'video'],
        ],
        'split' => [
            'tag'          => ['label' => 'Eyebrow Tag (e.g. Plan Your Day)', 'type' => 'text'],
            'title'        => ['label' => 'Title', 'type' => 'text'],
            'description'  => ['label' => 'Description', 'type' => 'textarea'],
            'location_tag' => ['label' => 'Location Tag (e.g. Lima Technology Center · Lipa City)', 'type' => 'text'],
        ],
        'contact' => [
            'location'      => ['label' => 'Location Address', 'type' => 'text'],
            'hours_weekend' => ['label' => 'Weekend Hours', 'type' => 'text'],
            'hours_weekday' => ['label' => 'Weekday Hours', 'type' => 'text'],
            'zones'         => ['label' => 'Zones (comma-separated)', 'type' => 'text'],
        ],
        'footer' => [
            'tagline' => ['label' => 'Footer Tagline', 'type' => 'textarea'],
        ],
    ];

    /** Tags allowed to survive out of the rich-text editor. */
    private string $richtextAllowedTags = '<b><strong><i><em><u><span><br>';

    private array $cardTypes = ['pass', 'attraction', 'service', 'step'];

    /**
     * Build the card-count map used by both the CMS dashboard and the
     * cards-index view (its "Card Collections" summary section links
     * out to every card type, not just the one currently being viewed).
     */
    private function cardCounts(): array
    {
        $cardCounts = [];
        foreach ($this->cardTypes as $type) {
            $cardCounts[$type] = SiteCard::type($type)->count();
        }

        return $cardCounts;
    }

    /**
     * CMS dashboard — links to every editable section.
     */
    public function index()
    {
        return view('cms.index', [
            'sections'    => array_keys($this->sectionFields),
            'cardCounts'  => $this->cardCounts(),
        ]);
    }

    /**
     * Edit a simple text section (hero, split, contact, footer).
     */
    public function editSection(string $section)
    {
        abort_unless(isset($this->sectionFields[$section]), 404);

        $fields = $this->sectionFields[$section];
        $values = SiteContent::section($section);

        return view('cms.edit-section', compact('section', 'fields', 'values'));
    }

    public function updateSection(Request $request, string $section)
    {
        abort_unless(isset($this->sectionFields[$section]), 404);

        foreach ($this->sectionFields[$section] as $key => $meta) {
            $type = $meta['type'] ?? 'text';

            if ($type === 'video') {
                // No new file chosen -> keep whatever is already saved.
                if (!$request->hasFile($key)) {
                    continue;
                }

                $request->validate([
                    $key => 'file|mimetypes:video/mp4,video/webm,video/quicktime|max:51200', // 50MB
                ]);

                $path = $request->file($key)->store('hero-videos', 'public');
                SiteContent::set($section, $key, '/storage/' . $path);
                continue;
            }

            if ($type === 'richtext') {
                // Only richtext fields come out of a contenteditable
                // editor, so only they need HTML stripped down to the
                // allowed formatting tags before being stored.
                $clean = strip_tags((string) $request->input($key), $this->richtextAllowedTags);
                SiteContent::set($section, $key, $clean);
                continue;
            }

            // 'text' and 'textarea' fields are plain inputs — store the
            // value as-is (Blade escapes it wherever it's displayed).
            SiteContent::set($section, $key, $request->input($key));
        }

        return redirect()->route('cms.section.edit', $section)->with('success', ucfirst($section) . ' section updated.');
    }

    /**
     * List cards of a given type (pass / attraction / service / step).
     */
    public function cards(string $type)
    {
        abort_unless(in_array($type, $this->cardTypes), 404);

        $cards = SiteCard::type($type)->ordered()->get();

        return view('cms.cards-index', [
            'type'       => $type,
            'label'      => SiteCard::typeLabel($type),
            'cards'      => $cards,
            'cardCounts' => $this->cardCounts(),
        ]);
    }

    public function createCard(string $type)
    {
        abort_unless(in_array($type, $this->cardTypes), 404);

        return view('cms.card-form', [
            'type'  => $type,
            'label' => SiteCard::typeLabel($type),
            'card'  => null,
        ]);
    }

    public function editCard(SiteCard $card)
    {
        return view('cms.card-form', [
            'type'  => $card->type,
            'label' => SiteCard::typeLabel($card->type),
            'card'  => $card,
        ]);
    }

    public function storeCard(Request $request, string $type)
    {
        abort_unless(in_array($type, $this->cardTypes), 404);

        $data = $this->validateCard($request);
        $data['type'] = $type;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('site-cards', 'public');
        }

        if ($request->hasFile('icon_image')) {
            $data['icon_image_path'] = $this->storeResizedIcon($request->file('icon_image'));
        }

        SiteCard::create($data);
        Cache::flush();

        return redirect()->route('cms.cards', $type)->with('success', 'Added successfully.');
    }

    public function updateCard(Request $request, SiteCard $card)
    {
        $data = $this->validateCard($request);

        if ($request->hasFile('image')) {
            if ($card->image_path) {
                Storage::disk('public')->delete($card->image_path);
            }
            $data['image_path'] = $request->file('image')->store('site-cards', 'public');
        }

        if ($request->hasFile('icon_image')) {
            if ($card->icon_image_path) {
                Storage::disk('public')->delete($card->icon_image_path);
            }
            $data['icon_image_path'] = $this->storeResizedIcon($request->file('icon_image'));
        }

        $card->update($data);
        Cache::flush();

        return redirect()->route('cms.cards', $card->type)->with('success', 'Updated successfully.');
    }

    public function destroyCard(SiteCard $card)
    {
        $type = $card->type;

        if ($card->image_path) {
            Storage::disk('public')->delete($card->image_path);
        }
        if ($card->icon_image_path) {
            Storage::disk('public')->delete($card->icon_image_path);
        }
        $card->delete();
        Cache::flush();

        return redirect()->route('cms.cards', $type)->with('success', 'Deleted.');
    }

    private function validateCard(Request $request): array
{
    $data = $request->validate([
        'slug'           => 'required|string|max:100',
        'badge_label'    => 'nullable|string|max:100',
        'badge_color'    => 'nullable|in:coral,teal,amber,violet',
        'icon_type'      => 'nullable|in:fa,image',
        'icon'           => 'nullable|string|max:50',
        'icon_image'     => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        'title'          => 'required|string|max:150',
        'description'    => 'nullable|string',
        'price_display'  => 'nullable|string|max:50',
        'button_text'    => 'nullable|string|max:50',
        'button_link'    => 'nullable|string|max:255',
        'sort_order'     => 'nullable|integer',
        'image'          => 'nullable|image|max:4096',
    ]);

    unset($data['icon_image']);
    $data['icon_type']   = $data['icon_type'] ?? 'fa';
    $data['badge_color'] = $data['badge_color'] ?? 'coral';

    $data['features']   = $this->linesToArray($request->input('features_text'));
    $data['modal_list'] = $this->linesToArray($request->input('modal_list_text'));

    $data['is_featured'] = $request->boolean('is_featured');
    $data['is_active']   = $request->boolean('is_active');

    return $data;
}

    private function storeResizedIcon(\Illuminate\Http\UploadedFile $file): string
    {
        $targetSize = 128; // px, square

        $source = match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png'               => imagecreatefrompng($file->getRealPath()),
            'image/webp'              => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($file->getRealPath()) : null,
            default                   => null,
        };

        // If GD can't decode it for some reason, fall back to storing the
        // original file untouched rather than losing the upload.
        if (!$source) {
            return $file->store('site-icons', 'public');
        }

        $srcWidth  = imagesx($source);
        $srcHeight = imagesy($source);
        $cropSize  = min($srcWidth, $srcHeight);
        $srcX      = (int) (($srcWidth - $cropSize) / 2);
        $srcY      = (int) (($srcHeight - $cropSize) / 2);

        $canvas = imagecreatetruecolor($targetSize, $targetSize);
        imagesavealpha($canvas, true);
        imagealphablending($canvas, false);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagealphablending($canvas, true);

        imagecopyresampled(
            $canvas, $source,
            0, 0, $srcX, $srcY,
            $targetSize, $targetSize,
            $cropSize, $cropSize
        );

        $relativePath = 'site-icons/' . uniqid('icon_') . '.png';
        $absoluteDir  = storage_path('app/public/site-icons');

        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        imagepng($canvas, storage_path('app/public/' . $relativePath));

        imagedestroy($source);
        imagedestroy($canvas);

        return $relativePath;
    }

    private function linesToArray(?string $text): ?array
    {
        if (!$text) {
            return null;
        }

        return collect(explode("\n", $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}