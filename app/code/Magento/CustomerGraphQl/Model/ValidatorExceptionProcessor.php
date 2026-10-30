<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Message\AbstractMessage;
use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * Helper class to process ValidatorException and translate individual messages for GraphQL
 */
class ValidatorExceptionProcessor
{
    /**
     * Process InputException and return GraphQlInputException for GraphQL responses
     *
     * @param InputException $exception
     * @param string $separator
     * @return GraphQlInputException
     */
    public function processInputExceptionForGraphQl(
        InputException $exception,
        string $separator = ' '
    ): GraphQlInputException {
        if ($exception instanceof ValidatorException) {
            return $this->processValidatorExceptionForGraphQl($exception, $separator);
        } else {
            return $this->processStandardInputExceptionForGraphQl($exception, $separator);
        }
    }

    /**
     * Process ValidatorException and return GraphQlInputException for GraphQL responses
     *
     * @param ValidatorException $exception
     * @param string $separator
     * @return GraphQlInputException
     */
    public function processValidatorExceptionForGraphQl(
        ValidatorException $exception,
        string $separator = ' '
    ): GraphQlInputException {
        $validatorMessages = $exception->getMessages();
        if (empty($validatorMessages)) {
            return new GraphQlInputException(__($exception->getMessage()), $exception);
        }

        $translatedMessages = [];
        foreach ($validatorMessages as $message) {
            $messageText = $message instanceof AbstractMessage
                ? $message->getText()
                : (string)$message;
            $translatedMessages[] = (string)__($messageText);
        }

        $combinedTranslatedMessage = implode($separator, $translatedMessages);
        return new GraphQlInputException(__($combinedTranslatedMessage), $exception);
    }

    /**
     * Process standard InputException and return GraphQlInputException for GraphQL responses
     *
     * @param InputException $exception
     * @param string $separator
     * @return GraphQlInputException
     */
    public function processStandardInputExceptionForGraphQl(
        InputException $exception,
        string $separator = ' '
    ): GraphQlInputException {
        $errors = $exception->getErrors();
        if (!empty($errors)) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = (string)$error->getMessage();
            }
            $errorMessage = implode($separator, $errorMessages);
            return new GraphQlInputException(__($errorMessage), $exception);
        } else {
            return new GraphQlInputException(__($exception->getMessage()), $exception);
        }
    }
}
