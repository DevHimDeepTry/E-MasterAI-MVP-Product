<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IMVPService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MVPController extends Controller
{
    public function __construct(
        private readonly IMVPService $mvpService
    ){ }

    public function getTopics(Request $request):JsonResponse
    {
        $subject = $request->query('subject');

        $topics  = $this->mvpService->getTopicsOf($subject);

        return response()->json($topics, 200);
    }

    public function generate(Request $request): StreamedResponse
    {
        $type = $request->input('type');
        $data = [];
        $response = "";
        if ($type == "sendMessage") {
            $data = [
                'type' => $request->input('type'),
                'content' => $request->input('content'),
                'history' => $request->input('history'),
            ];
            $response = $this->mvpService->proccessRequest($data);
        } else {
            $data = [
                'subject' => $request->input('subject'),
                'gradeLevel' => $request->input('gradeLevel'),
                'topic' => $request->input('topic'),
                'periods' => $request->input('periods'),
                'duration' => $request->input('duration'),
                'type' => $request->input('type'),
                'file' => $request->input('file'),
            ];
        }

        if ($type == "generate_slide") {
            $response = $this->mvpService->generateSlide($data);
        }
        else if ($type == "generate_lesson_plan") {
            $response = $this->mvpService->generateLessonPlan($data);
        }

        return response()->stream(function () use ($response) {
            echo $response;
        });
    }
    public function downloadLessonPlan(Request $lessonPlanContent)
    {
        $lessonPlanContent = $lessonPlanContent->input('fileLessonPlan');
        return $this->mvpService->generatePdf($lessonPlanContent);
    }
}
