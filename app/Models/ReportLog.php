<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportLog extends Model
{
    use HasFactory;

    protected $table = 'report_logs';

    protected $fillable = [
        'user_id',
        'type',
        'format',
        'date_debut',
        'date_fin',
        'generated_at',
    ];

    protected $dates = [
        'date_debut',
        'date_fin',
        'generated_at',
    ];

    // Relation avec User (facultative mais utile)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
