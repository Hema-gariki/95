<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    protected $table    = 'responses';
    protected $fillable = [
        'survey_id', 'question_id',
        'session_id',       // Random UUID — identifies one submission, not a person
        'selected_answer', 'is_correct', 'submitted_at',
    ];
    protected $casts = ['is_correct' => 'boolean', 'submitted_at' => 'datetime'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
