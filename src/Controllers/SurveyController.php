<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Question;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;
use Slim\Views\Twig;

class SurveyController
{
    public function __construct(private Twig $view) {}

    /** Show the quiz to the participant */
    public function show(Request $request, Response $response, array $args): Response
    {
        $survey = Survey::where('token', $args['token'])->first();

        if (!$survey) {
            return $this->view->render($response->withStatus(404), 'error.twig',
                ['code' => 404, 'message' => 'Survey not found.']);
        }

        if (!$survey->is_active) {
            return $this->view->render($response->withStatus(403), 'error.twig',
                ['code' => 403, 'message' => 'This survey is currently unavailable.']);
        }

        // Fetch questions from DB and shuffle options for each one
        $questions = Question::where('survey_id', $survey->id)->orderBy('order')->get()
            ->map(fn($q) => [
                'id'      => $q->id,
                'text'    => $q->question_text,
                'options' => $q->getShuffledOptions(),   // correct + wrong shuffled
            ]);

        return $this->view->render($response, 'survey/show.twig', [
            'survey'    => $survey,
            'questions' => $questions,
        ]);
    }

    /** Handle the participant's form submission */
    public function submit(Request $request, Response $response, array $args): Response
    {
        $survey = Survey::where('token', $args['token'])->first();
        if (!$survey || !$survey->is_active) {
            return $response->withStatus(403);
        }

        $answers   = (array)($request->getParsedBody()['answers'] ?? []);
        $questions = Question::where('survey_id', $survey->id)->get()->keyBy('id');
        $sessionId = Uuid::uuid4()->toString();   // anonymous — one UUID per submission
        $now       = date('Y-m-d H:i:s');

        foreach ($answers as $qId => $chosen) {
            $q = $questions->get((int)$qId);
            if (!$q) continue;
            SurveyResponse::create([
                'survey_id'       => $survey->id,
                'question_id'     => $q->id,
                'session_id'      => $sessionId,
                'selected_answer' => $chosen,
                'is_correct'      => strtolower(trim($chosen)) === strtolower(trim($q->correct_answer)),
                'submitted_at'    => $now,
            ]);
        }

        // Store score in session just long enough to show on thank-you page
        $_SESSION['_score'] = SurveyResponse::where('session_id', $sessionId)->where('is_correct', true)->count();
        $_SESSION['_total'] = count($answers);

        return $response->withHeader('Location', "/survey/{$args['token']}/thankyou")->withStatus(302);
    }

    /** Show the score after submission */
    public function thankyou(Request $request, Response $response, array $args): Response
    {
        $survey = Survey::where('token', $args['token'])->first();
        if (!$survey) return $response->withHeader('Location', '/')->withStatus(302);

        $score = $_SESSION['_score'] ?? null;
        $total = $_SESSION['_total'] ?? null;
        unset($_SESSION['_score'], $_SESSION['_total']);

        return $this->view->render($response, 'survey/thankyou.twig', [
            'survey' => $survey,
            'score'  => $score,
            'total'  => $total,
        ]);
    }
}
