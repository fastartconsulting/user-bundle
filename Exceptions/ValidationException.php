<?php

namespace Exceptions;


use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException {

    /** @var TranslatorInterface $translator */
    private $translator;

    /**
     * ValidationException constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator){
        $this->translator = $translator;
    }

    /**
     * @param ConstraintViolationListInterface $constraintViolationList
     * @return array
     */
    public function getFormattedExceptions(ConstraintViolationListInterface $constraintViolationList) {

        $message = array();

        foreach ($constraintViolationList as $violation){
            $message[$violation->getPropertyPath()] = $this->translator->trans($violation->getMessage());
        }

        return $message;
    }

}