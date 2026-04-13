<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table    = 'questions';
    protected $fillable = ['survey_id', 'question_text', 'correct_answer', 'wrong_options', 'order'];

    // Do NOT use $casts here — we decode manually to avoid issues
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get wrong_options as a proper array always.
     * Handles both JSON string and already-decoded array.
     */
    public function getWrongOptionsArray(): array
    {
        $val = $this->wrong_options;

        if (is_array($val)) {
            return $val;
        }

        if (is_string($val)) {
            $decoded = json_decode($val, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Returns all answer options (correct + wrong) shuffled together.
     * This is what participants see — they cannot tell which is correct.
     */
    public function getShuffledOptions(): array
    {
        $wrong   = $this->getWrongOptionsArray();
        $options = array_merge([$this->correct_answer], $wrong);
        shuffle($options);
        return $options;
    }
}