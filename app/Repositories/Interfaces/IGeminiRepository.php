<?php

namespace App\Repositories\Interfaces;


use Gemini\Responses\StreamResponse;

interface IGeminiRepository
{
    function sendMessage(string $message): string;
    function getTopics($subject):array;
    function streamGenerateResponse($promptProccessRequest):string ;
}
