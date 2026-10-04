<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Message\AbstractMessage;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * Helper class to process ValidatorException and translate individual messages
 */
class ValidatorExceptionProcessor
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param ManagerInterface|null $messageManager
     */
    public function __construct(
        ?ManagerInterface $messageManager = null
    ) {
        $this->messageManager = $messageManager;
    }

    /**
     * Set message manager
     *
     * @param ManagerInterface $messageManager
     * @return void
     */
    public function setMessageManager(ManagerInterface $messageManager): void
    {
        $this->messageManager = $messageManager;
    }

    /**
     * Process InputException and add messages to message manager
     *
     * @param InputException $exception
     * @param callable|null $messageFormatter
     * @param string $method
     * @return void
     */
    public function processInputException(
        InputException $exception,
        ?callable $messageFormatter = null,
        string $method = 'addErrorMessage'
    ): void {
        if ($exception instanceof ValidatorException) {
            $this->processValidatorException($exception, $messageFormatter, $method);
        } else {
            $this->processStandardInputException($exception, $messageFormatter, $method);
        }
    }

    /**
     * Process ValidatorException by extracting, translating and merging again individual messages
     *
     * @param ValidatorException $exception
     * @param callable|null $messageFormatter
     * @param string $method
     * @return void
     */
    public function processValidatorException(
        ValidatorException $exception,
        ?callable $messageFormatter = null,
        string $method = 'addErrorMessage'
    ): void {
        $validatorMessages = $exception->getMessages();
        if (empty($validatorMessages)) {
            $message = $exception->getMessage();
            if ($messageFormatter) {
                $message = $messageFormatter($message);
            }
            $this->addMessage($message, $method);
            return;
        }

        $translatedMessages = [];
        foreach ($validatorMessages as $message) {
            $messageText = $message instanceof AbstractMessage
                ? $message->getText()
                : (string)$message;
            $translatedMessages[] = (string)__($messageText);
        }

        $combinedTranslatedMessage = implode(' ', $translatedMessages);
        if ($messageFormatter) {
            $combinedTranslatedMessage = $messageFormatter($combinedTranslatedMessage);
        }
        $this->addMessage($combinedTranslatedMessage, $method);
    }

    /**
     * Process standard InputException by extracting individual errors
     *
     * @param InputException $exception
     * @param callable|null $messageFormatter
     * @param string $method
     * @return void
     */
    public function processStandardInputException(
        InputException $exception,
        ?callable $messageFormatter = null,
        string $method = 'addErrorMessage'
    ): void {
        $errors = $exception->getErrors();
        if (!empty($errors)) {
            $message = $exception->getMessage();
            if ($messageFormatter) {
                $message = $messageFormatter($message);
            }
            $this->addMessage($message, $method);
            foreach ($errors as $error) {
                $errorMessage = $error->getMessage();
                if ($messageFormatter) {
                    $errorMessage = $messageFormatter($errorMessage);
                }
                $this->addMessage($errorMessage, $method);
            }
        } else {
            $message = $exception->getMessage();
            if ($messageFormatter) {
                $message = $messageFormatter($message);
            }
            $this->addMessage($message, $method);
        }
    }

    /**
     * Add message using the specified method
     *
     * @param string|Phrase $message
     * @param string $method
     * @return void
     */
    private function addMessage(Phrase|string $message, string $method): void
    {
        if ($this->messageManager === null) {
            return;
        }

        switch ($method) {
            case 'addError':
                $this->messageManager->addError($message);
                break;
            case 'addErrorMessage':
            default:
                $this->messageManager->addErrorMessage($message);
                break;
        }
    }

    /**
     * Process InputException and return Phrase for JSON responses
     *
     * @param InputException $exception
     * @return Phrase
     */
    public function processInputExceptionForJson(InputException $exception): Phrase
    {
        if ($exception instanceof ValidatorException) {
            return $this->processValidatorExceptionForJson($exception);
        } else {
            return $this->processStandardInputExceptionForJson($exception);
        }
    }

    /**
     * Process ValidatorException and return Phrase for JSON responses
     *
     * @param ValidatorException $exception
     * @return Phrase
     */
    public function processValidatorExceptionForJson(ValidatorException $exception): Phrase
    {
        $validatorMessages = $exception->getMessages();
        if (empty($validatorMessages)) {
            return __($exception->getMessage());
        }

        $translatedMessages = [];
        foreach ($validatorMessages as $message) {
            $messageText = $message instanceof AbstractMessage
                ? $message->getText()
                : (string)$message;
            $translatedMessages[] = (string)__($messageText);
        }

        $combinedTranslatedMessage = implode(' ', $translatedMessages);
        return __($combinedTranslatedMessage);
    }

    /**
     * Process standard InputException and return Phrase for JSON responses
     *
     * @param InputException $exception
     * @return Phrase
     */
    public function processStandardInputExceptionForJson(InputException $exception): Phrase
    {
        $errors = $exception->getErrors();
        if (!empty($errors)) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = (string)$error->getMessage();
            }
            return __(implode(' ', $errorMessages));
        } else {
            return __($exception->getMessage());
        }
    }

    /**
     * Process ValidatorException for REST API and return errors array and main phrase
     *
     * @param ValidatorException $exception
     * @param string $separator
     * @return array{errors: array|null, mainPhrase: Phrase}
     */
    public function processValidatorExceptionForRestApi(
        ValidatorException $exception,
        string $separator = "\n"
    ): array {
        $validatorMessages = $exception->getMessages();
        if (empty($validatorMessages)) {
            return [
                'errors' => null,
                'mainPhrase' => new Phrase($exception->getRawMessage())
            ];
        }

        $errors = [];
        $translatedMessages = [];
        foreach ($validatorMessages as $message) {
            $messageText = $message instanceof AbstractMessage
                ? $message->getText()
                : (string)$message;
            $translatedMessagePhrase = __($messageText);
            $translatedString = (string)$translatedMessagePhrase;
            $translatedMessages[] = $translatedString;
            $errors[] = new \Magento\Framework\Exception\LocalizedException(new Phrase($translatedString));
        }
        $combinedTranslatedMessage = implode($separator, $translatedMessages);
        $mainPhrase = new Phrase($combinedTranslatedMessage);

        return [
            'errors' => $errors,
            'mainPhrase' => $mainPhrase
        ];
    }
}
