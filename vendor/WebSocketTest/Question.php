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

    private $id;
    private $questionText;
    private $image;
    private $subject;
    private $type;
    private $multipleChoiceAnswers = array();
    private $correctAnswer;

    public function getId()
    {
        return $this->id;
    }

    public function getQuestionText()
    {
        return $this->questionText;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getSubject()
    {
        return $this->subject;
    }
    
    public function getType()
    {
        return $this->type;
    }

    public function getMultipleChoiceAnswers()
    {
        return $this->multipleChoiceAnswers;
    }

    public function getCorrectAnswer()
    {
        return $this->correctAnswer;
    }

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
        if ($answer != $this->correctAnswer)
        {
            return false;
        }
        return true;
    }
}
