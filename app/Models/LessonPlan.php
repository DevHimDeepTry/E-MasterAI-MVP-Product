<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPlan extends Model
{
    use HasFactory;
    private string $subject;
    private string $gradeLevel;
    private string $topic;
    private int $numberLessons;
    private int $timeline;

    function  __construct(
        string $subject,
        string $gradeLevel,
        string $topic,
        int $numberLessons,
        int $timeline
    )
    {
        $this->subject = $subject;
        $this->gradeLevel = $gradeLevel;
        $this->topic = $topic;
        $this->numberLessons = $numberLessons;
        $this->timeline = $timeline;
    }
}
