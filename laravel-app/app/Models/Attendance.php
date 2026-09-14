<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';
    public $timestamps = false;

    protected $fillable = [
        'employee_id', 'employee_name', 'category',
        'attendance_date', 'time_in', 'time_out', 'status', 'total_hours',
    ];
}