<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Question
 *
 * @author Gebruiker
 */

namespace WebSocketTest;

class Question
{
    public $id;
    public $questionText;
    public $image;
    public $subject;
    public $type;
    public $multipleChoiceAnswers = array();
    public $correctAnswer;

    function __construct($id, $questionText, $image, $subject, $type, $multipleChoiceAnswers, $correctAnswer)
    {
        $this->id = $id;
        $this->questionText = $questionText;
        $this->image = $image;
        $this->subject = $subject;
        $this->type = $type;
        $this->multipleChoiceAnswers = $multipleChoiceAnswers;
        $this->correctAnswer = $correctAnswer;
    }

    public function checkAnswer($answer)
    {
        $givenAnswer = strtolower($answer);
        $correctAnswer = strip_tags(strtolower($this->multipleChoiceAnswers[$this->correctAnswer]));
        echo "Correct answer: ".$this->multipleChoiceAnswers[$this->correctAnswer]."\n";
        if ($givenAnswer != $correctAnswer)
        {
            return false;
        }
        return true;
    }
}
