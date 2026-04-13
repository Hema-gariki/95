<?php
/**
 * SurveyFlow — Database Setup
 *
 * Usage:
 *   php database/migrate.php                       # create tables only
 *   php database/migrate.php --seed                # tables + admin user
 *   php database/migrate.php --fresh               # drop all + recreate
 *   php database/migrate.php --fresh --seed        # full reset + seed
 *   php database/migrate.php --fresh --seed --demo # full reset + seed + demo data
 */

declare(strict_types=1);

// ── STEP 1: Check vendor folder exists ────────────────────────────────────
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    echo "\n❌ ERROR: vendor/ folder not found.\n";
    echo "   Fix: run  composer install  first.\n\n";
    exit(1);
}
require $autoload;

// ── STEP 2: Load .env file (auto-copy from .env.example if missing) ────────
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    $example = __DIR__ . '/../.env.example';
    if (file_exists($example)) {
        copy($example, $envFile);
        echo "ℹ  .env not found — auto-copied from .env.example\n";
    } else {
        echo "\n❌ ERROR: .env file missing.\n";
        echo "   Fix: run  cp .env.example .env\n\n";
        exit(1);
    }
}
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// ── STEP 3: Boot Eloquent database connection ──────────────────────────────
require __DIR__ . '/../src/bootstrap/database.php';

use Illuminate\Database\Capsule\Manager as DB;

$args  = $argv ?? [];
$fresh = in_array('--fresh', $args);
$seed  = in_array('--seed',  $args);
$demo  = in_array('--demo',  $args);

echo "\n🗄  SurveyFlow — Database Setup\n";
echo str_repeat('─', 42) . "\n\n";

// ── Drop tables if --fresh (reverse order due to foreign keys) ─────────────
if ($fresh) {
    echo "⚠  Dropping all tables...\n";
    DB::schema()->dropIfExists('responses');
    DB::schema()->dropIfExists('questions');
    DB::schema()->dropIfExists('surveys');
    DB::schema()->dropIfExists('users');
    echo "   Done.\n\n";
}

// ── TABLE: users ───────────────────────────────────────────────────────────
if (!DB::schema()->hasTable('users')) {
    DB::schema()->create('users', function ($t) {
        $t->id();
        $t->string('name');
        $t->string('email')->unique();
        $t->string('password');
        $t->timestamps();
    });
    echo "✓ Created table: users\n";
} else {
    echo "  Skipped (exists): users\n";
}

// ── TABLE: surveys ─────────────────────────────────────────────────────────
// title       = unique topic name entered by admin
// token       = UUID used in public URL: /survey/{token}
// is_active   = 1 = on, 0 = off (admin can toggle)
// csv_filename= original uploaded file kept in storage/uploads/
if (!DB::schema()->hasTable('surveys')) {
    DB::schema()->create('surveys', function ($t) {
        $t->id();
        $t->string('title')->unique();
        $t->string('token', 36)->unique();
        $t->boolean('is_active')->default(true);
        $t->string('csv_filename')->nullable();
        $t->timestamps();
    });
    echo "✓ Created table: surveys\n";
} else {
    echo "  Skipped (exists): surveys\n";
}

// ── TABLE: questions ───────────────────────────────────────────────────────
// question_text  = e.g. "CPU stands for Central Processing Unit."
// correct_answer = e.g. "TRUE"  or  "Stack"
// wrong_options  = JSON array e.g. ["FALSE"]  or  ["Queue","Array","Linked List"]
// order          = row number from CSV (preserves original order)
if (!DB::schema()->hasTable('questions')) {
    DB::schema()->create('questions', function ($t) {
        $t->id();
        $t->unsignedBigInteger('survey_id');
        $t->text('question_text');
        $t->string('correct_answer');
        $t->text('wrong_options');
        $t->unsignedSmallInteger('order')->default(1);
        $t->timestamps();
        $t->foreign('survey_id')
          ->references('id')->on('surveys')
          ->onDelete('cascade');
    });
    echo "✓ Created table: questions\n";
} else {
    echo "  Skipped (exists): questions\n";
}

// ── TABLE: responses ───────────────────────────────────────────────────────
// FULLY ANONYMOUS — no names, no emails, no IP addresses stored.
// session_id     = random UUID generated at submission time (groups one person's answers)
// selected_answer= what the participant chose
// is_correct     = 1 if answer matched correct_answer, 0 if not
// submitted_at   = timestamp of when they submitted
if (!DB::schema()->hasTable('responses')) {
    DB::schema()->create('responses', function ($t) {
        $t->id();
        $t->unsignedBigInteger('survey_id');
        $t->unsignedBigInteger('question_id');
        $t->string('session_id', 36)->index();
        $t->text('selected_answer');
        $t->boolean('is_correct')->default(false);
        $t->timestamp('submitted_at')->nullable();
        $t->timestamps();
        $t->foreign('survey_id')
          ->references('id')->on('surveys')
          ->onDelete('cascade');
        $t->foreign('question_id')
          ->references('id')->on('questions')
          ->onDelete('cascade');
    });
    echo "✓ Created table: responses\n";
} else {
    echo "  Skipped (exists): responses\n";
}

echo "\n✅ All tables ready.\n";

// ── Seed admin user ────────────────────────────────────────────────────────
if ($seed) {
    echo "\n── Seeding admin user ─────────────────────\n";

    $email    = $_ENV['ADMIN_EMAIL']    ?? 'admin@surveyflow.test';
    $password = $_ENV['ADMIN_PASSWORD'] ?? 'password';
    $hash     = password_hash($password, PASSWORD_BCRYPT);
    $now      = date('Y-m-d H:i:s');

    if (DB::table('users')->where('email', $email)->exists()) {
        DB::table('users')->where('email', $email)
            ->update(['password' => $hash, 'updated_at' => $now]);
        echo "✓ Admin updated: {$email}\n";
    } else {
        DB::table('users')->insert([
            'name'       => 'Admin',
            'email'      => $email,
            'password'   => $hash,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        echo "✓ Admin created: {$email}\n";
    }
    echo "  Password: {$password}\n";
    echo "⚠  Change this password after first login!\n";
}

// ── Insert demo data from sample_survey.csv ────────────────────────────────
if ($demo) {
    echo "\n── Inserting demo survey data ─────────────\n";

    $csvPath = __DIR__ . '/../sample_survey.csv';

    if (!file_exists($csvPath)) {
        echo "⚠  sample_survey.csv not found — skipping.\n";
    } else {
        // Remove old demo survey if it exists
        $old = DB::table('surveys')->where('title', 'Computer Science Basics')->first();
        if ($old) {
            DB::table('responses')->where('survey_id', $old->id)->delete();
            DB::table('questions')->where('survey_id', $old->id)->delete();
            DB::table('surveys')->where('id', $old->id)->delete();
            echo "  Removed old demo survey.\n";
        }

        // Create survey record
        $now      = date('Y-m-d H:i:s');
        $token    = makeUuid();
        $surveyId = DB::table('surveys')->insertGetId([
            'title'        => 'Computer Science Basics',
            'token'        => $token,
            'is_active'    => true,
            'csv_filename' => 'sample_survey.csv',
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        // Parse CSV line by line
        $handle  = fopen($csvPath, 'r');
        $lineNum = 0;
        $count   = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNum++;

            // Skip blank rows
            $notEmpty = array_filter($row, fn($c) => trim($c) !== '');
            if (empty($notEmpty)) continue;

            // Skip header row (first row where column 0 contains the word "question")
            if ($lineNum === 1 && stripos(trim($row[0]), 'question') !== false) continue;

            // Need minimum 3 columns: question, correct answer, at least 1 wrong option
            if (count($row) < 3) continue;

            $question = trim($row[0]);
            $correct  = trim($row[1]);

            // Collect all wrong options from column 3 onwards (skip empty cells)
            $wrong = [];
            for ($i = 2; $i < count($row); $i++) {
                $val = trim($row[$i]);
                if ($val !== '') {
                    $wrong[] = $val;
                }
            }

            if (empty($wrong)) continue;

            $count++;
            DB::table('questions')->insert([
                'survey_id'      => $surveyId,
                'question_text'  => $question,
                'correct_answer' => $correct,
                'wrong_options'  => json_encode($wrong),
                'order'          => $count,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
        fclose($handle);

        $base = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');

        echo "✓ Survey created: \"Computer Science Basics\" ({$count} questions)\n";
        echo "  Share this URL: {$base}/survey/{$token}\n\n";

        echo "  Questions in database:\n";
        $qs = DB::table('questions')
            ->where('survey_id', $surveyId)
            ->orderBy('order')
            ->get();

        foreach ($qs as $q) {
            $wrongList = json_decode($q->wrong_options, true);
            echo "  ┌ Q{$q->order}: {$q->question_text}\n";
            echo "  │  ✓ Correct : {$q->correct_answer}\n";
            echo "  └  ✗ Wrong   : " . implode(' | ', $wrongList) . "\n";
        }
    }
}

echo "\n";
echo "─────────────────────────────────────────\n";
echo "Done! Next steps:\n";
echo "  php -S localhost:8000 -t public\n";
echo "  Open: http://localhost:8000/admin/login\n";
echo "─────────────────────────────────────────\n\n";


function makeUuid(): string
{
    if (class_exists('\Ramsey\Uuid\Uuid')) {
        return \Ramsey\Uuid\Uuid::uuid4()->toString();
    }
    // Pure PHP fallback using random_bytes (no dependencies needed)
    $bytes    = random_bytes(16);
    $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
    $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);
    return vsprintf(
        '%s%s-%s-%s-%s-%s%s%s',
        str_split(bin2hex($bytes), 4)
    );
}
