<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $table    = 'surveys';
    protected $fillable = ['title', 'token', 'is_active', 'csv_filename'];
    protected $casts    = ['is_active' => 'boolean'];

    // Auto-generate UUID token before insert
    protected static function booted(): void
    {
        static::creating(function (Survey $s) {
            if (empty($s->token)) {
                $s->token = generateUuid();
            }
        });
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function getSurveyUrl(): string
    {
        return rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/') . '/survey/' . $this->token;
    }

    public function getRespondentCount(): int
    {
        return SurveyResponse::where('survey_id', $this->id)
            ->distinct('session_id')->count('session_id');
    }
}

// UUID generator — no external package needed
function generateUuid(): string
{
    if (class_exists('\Ramsey\Uuid\Uuid')) {
        return \Ramsey\Uuid\Uuid::uuid4()->toString();
    }
    $bytes    = random_bytes(16);
    $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
    $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
}