<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Question;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AdminController
{
    public function __construct(private Twig $view) {}

    public function dashboard(Request $request, Response $response): Response
    {
        $surveys = Survey::orderBy('created_at', 'desc')->get()->map(function ($s) {
            $s->respondent_count = $s->getRespondentCount();
            $s->question_count   = Question::where('survey_id', $s->id)->count();
            $s->survey_url       = $s->getSurveyUrl();
            return $s;
        });
        return $this->view->render($response, 'admin/dashboard.twig', ['surveys' => $surveys]);
    }

    public function showUpload(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'admin/upload.twig');
    }

    public function processUpload(Request $request, Response $response): Response
    {
        $body  = (array)$request->getParsedBody();
        $files = $request->getUploadedFiles();
        $title = trim($body['title'] ?? '');

        if (empty($title)) {
            $_SESSION['_flash']['error'] = 'Survey title is required.';
            return $response->withHeader('Location', '/admin/upload')->withStatus(302);
        }

        if (Survey::where('title', $title)->exists()) {
            $_SESSION['_flash']['error'] = "A survey titled \"{$title}\" already exists.";
            return $response->withHeader('Location', '/admin/upload')->withStatus(302);
        }

        $csvFile = $files['csv'] ?? null;
        if (!$csvFile || $csvFile->getError() !== UPLOAD_ERR_OK) {
            $_SESSION['_flash']['error'] = 'Please upload a valid CSV file.';
            return $response->withHeader('Location', '/admin/upload')->withStatus(302);
        }

        $uploadDir = __DIR__ . '/../../storage/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = preg_replace('/[^a-z0-9_-]/', '_', strtolower($title)) . '_' . time() . '.csv';
        $csvFile->moveTo($uploadDir . $filename);

        $rows = $this->parseCsv($uploadDir . $filename);

        if (empty($rows)) {
            $_SESSION['_flash']['error'] = 'The CSV file is empty or could not be read.';
            return $response->withHeader('Location', '/admin/upload')->withStatus(302);
        }

        // Generate UUID token directly here
        $token = sprintf(
            '%s-%s-%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(6))
        );

        // Create survey with token
        $survey = Survey::create([
            'title'        => $title,
            'token'        => $token,
            'is_active'    => true,
            'csv_filename' => $filename,
        ]);

        $stored = 0;
        foreach ($rows as $order => $row) {
            if (count($row) < 3) {
                continue;
            }

            $trimmed  = array_map('trim', $row);
            $question = $trimmed[0];
            $correct  = $trimmed[1];
            $wrong    = array_values(array_filter(
                array_slice($trimmed, 2),
                fn($o) => $o !== ''
            ));

            if (empty($wrong)) {
                continue;
            }

            Question::create([
                'survey_id'      => $survey->id,
                'question_text'  => $question,
                'correct_answer' => $correct,
                'wrong_options'  => json_encode($wrong),
                'order'          => $order + 1,
            ]);
            $stored++;
        }

        if ($stored === 0) {
            $survey->delete();
            $_SESSION['_flash']['error'] = 'No valid rows found. Format: Question, CorrectAnswer, WrongOption1, ...';
            return $response->withHeader('Location', '/admin/upload')->withStatus(302);
        }

        $_SESSION['_flash']['success'] = "Survey \"{$title}\" created with {$stored} questions! URL: {$survey->getSurveyUrl()}";
        return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
    }

    private function parseCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');
        if (!$handle) {
            return [];
        }

        $lineNum = 0;
        while (($cols = fgetcsv($handle)) !== false) {
            $lineNum++;
            if (empty(array_filter($cols, fn($c) => trim($c) !== ''))) {
                continue;
            }
            if ($lineNum === 1 && stripos(trim($cols[0]), 'question') !== false) {
                continue;
            }
            $rows[] = $cols;
        }

        fclose($handle);
        return $rows;
    }

    public function toggle(Request $request, Response $response, array $args): Response
    {
        $survey = Survey::findOrFail((int)$args['id']);
        $survey->update(['is_active' => !$survey->is_active]);
        $word = $survey->is_active ? 'activated' : 'deactivated';
        $_SESSION['_flash']['success'] = "Survey \"{$survey->title}\" has been {$word}.";
        return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $survey = Survey::findOrFail((int)$args['id']);
        $title  = $survey->title;
        $survey->delete();
        $_SESSION['_flash']['success'] = "Survey \"{$title}\" deleted.";
        return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
    }

    public function results(Request $request, Response $response, array $args): Response
    {
        $survey    = Survey::findOrFail((int)$args['id']);
        $questions = Question::where('survey_id', $survey->id)->orderBy('order')->get();

        $sessions = SurveyResponse::where('survey_id', $survey->id)
            ->orderBy('submitted_at')
            ->get()
            ->groupBy('session_id');

        $totalQ       = $questions->count();
        $participants = $sessions->values()->map(function ($rows) use ($totalQ) {
            $correct = $rows->where('is_correct', true)->count();
            return [
                'submitted_at' => optional($rows->first()->submitted_at)->format('Y-m-d H:i'),
                'score'        => $correct,
                'total'        => $totalQ,
                'percentage'   => $totalQ > 0 ? round(($correct / $totalQ) * 100) : 0,
                'answers'      => $rows->keyBy('question_id'),
            ];
        });

        return $this->view->render($response, 'admin/results.twig', [
            'survey'       => $survey,
            'questions'    => $questions,
            'participants' => $participants,
        ]);
    }

    public function downloadResults(Request $request, Response $response, array $args): Response
    {
        $survey    = Survey::findOrFail((int)$args['id']);
        $questions = Question::where('survey_id', $survey->id)->orderBy('order')->get();
        $sessions  = SurveyResponse::where('survey_id', $survey->id)
            ->orderBy('submitted_at')
            ->get()
            ->groupBy('session_id');

        $totalQ = $questions->count();
        $fp     = fopen('php://temp', 'r+');

        $header = ['Participant', 'Submitted At', 'Score', 'Percentage'];
        foreach ($questions as $q) {
            $header[] = "Q{$q->order}: {$q->question_text}";
            $header[] = "Q{$q->order}: Correct?";
        }
        fputcsv($fp, $header);

        $i = 1;
        foreach ($sessions as $sessionRows) {
            $byQ     = $sessionRows->keyBy('question_id');
            $correct = $sessionRows->where('is_correct', true)->count();
            $pct     = $totalQ > 0 ? round(($correct / $totalQ) * 100) . '%' : '0%';
            $row     = [
                "Participant {$i}",
                optional($sessionRows->first()->submitted_at)->format('Y-m-d H:i:s'),
                "{$correct}/{$totalQ}",
                $pct,
            ];
            foreach ($questions as $q) {
                $r     = $byQ->get($q->id);
                $row[] = $r ? $r->selected_answer : 'N/A';
                $row[] = $r ? ($r->is_correct ? 'Yes' : 'No') : 'N/A';
            }
            fputcsv($fp, $row);
            $i++;
        }

        rewind($fp);
        $csv  = stream_get_contents($fp);
        fclose($fp);
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($survey->title));

        $response->getBody()->write($csv);
        return $response
            ->withHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->withHeader('Content-Disposition', "attachment; filename=\"{$slug}_results.csv\"");
    }
}