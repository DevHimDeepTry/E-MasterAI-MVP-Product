<?php

namespace App\Services\Interfaces;


use Gemini\Responses\StreamResponse;

interface IMVPService
{
    function generateSlide($data): string;
    function getTopicsOf($subject): array;
    function generateLessonPlan($data): string;
    function proccessRequest($data): string;
    function generatePdf(string $htmlContent);
}
