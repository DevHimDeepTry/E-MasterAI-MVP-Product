<?php

namespace App\Services\Implements;

use App\prompts\Prompts;
use App\Repositories\Interfaces\IGeminiRepository;
use App\Services\Interfaces\IMVPService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Gemini\Responses\StreamResponse;

class MVPService implements IMVPService
{
    private IGeminiRepository $geminiRepository;
    public Prompts $prompts;
    public function __construct(IGeminiRepository $geminiRepository)
    {
        $this->geminiRepository = $geminiRepository;
        $this->prompts = new Prompts();
    }

    function proccessRequest($data): string
    {
        $promptProccessRequest = $this->prompts->proccessRequestPrompt($data);

        return $this->geminiRepository->streamGenerateResponse($promptProccessRequest);
    }
    function generateSlide($data):string
    {
        $promptGenerateSlide = $this->prompts->generateSlidePrompt($data);

        return $this->geminiRepository->sendMessage(($promptGenerateSlide));
    }
    function generateLessonPlan($data):string
    {
        $promptGenerateLessonPlan = $this->prompts->generateLessonPlanPrompt($data);

        return $this->geminiRepository->sendMessage(($promptGenerateLessonPlan));
    }

    function getTopicsOf($subject): array
    {
        return $this->geminiRepository->getTopics($subject);
    }

//    function generatePdf(string $htmlContent)
//    {
//        $options = new Options();
//        $options->set('defaultFont', 'Arial');
//        $dompdf = new Dompdf($options);
//        $dompdf->loadHtml($htmlContent);
//        $dompdf->setPaper('A4', 'portrait');
//        $dompdf->render();
//        return response($dompdf->output(), 200, [
//            'Content-Type' => 'application/pdf',
//            'Content-Disposition' => 'attachment; filename="lesson_plan.pdf"'
//        ]);
//    }
    function generatePdf(string $htmlContent)
    {

        // Cấu hình Dompdf
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isFontSubsettingEnabled', true); // Nhúng font vào file PDF
        $options->set('isHtml5ParserEnabled', true); // Hỗ trợ parser HTML5

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Trả về file PDF
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="lesson_plan.pdf"'
        ]);
    }
}
