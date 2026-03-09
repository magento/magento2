<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin\Webapi;

use Magento\Customer\Model\ValidatorExceptionProcessor;
use Magento\Framework\App\State;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Validator\Exception as ValidatorException;

class ErrorProcessorPlugin
{
    /**
     * @var ValidatorExceptionProcessor
     */
    private $validatorExceptionProcessor;

    /**
     * @var State
     */
    private $appState;

    /**
     * @param ValidatorExceptionProcessor $validatorExceptionProcessor
     * @param State $appState
     */
    public function __construct(
        ValidatorExceptionProcessor $validatorExceptionProcessor,
        State $appState
    ) {
        $this->validatorExceptionProcessor = $validatorExceptionProcessor;
        $this->appState = $appState;
    }

    /**
     * Process ValidatorException using ValidatorExceptionProcessor
     *
     * @param ErrorProcessor $subject
     * @param callable $proceed
     * @param \Exception $exception
     * @return WebapiException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundMaskException(
        ErrorProcessor $subject,
        callable $proceed,
        \Exception $exception
    ): WebapiException {
        if ($exception instanceof ValidatorException) {
            $validatorMessages = $exception->getMessages();
            if (empty($validatorMessages)) {
                return $proceed($exception);
            }
            if (count($validatorMessages) === 1) {
                return $proceed($exception);
            }

            $isDevMode = $this->appState->getMode() === State::MODE_DEVELOPER;
            $stackTrace = $isDevMode ? $exception->getTraceAsString() : null;
            $httpCode = WebapiException::HTTP_BAD_REQUEST;

            $result = $this->validatorExceptionProcessor->processValidatorExceptionForRestApi($exception);
            $errors = $result['errors'] ?? null;
            $mainPhrase = $result['mainPhrase'];

            return new WebapiException(
                $mainPhrase,
                $exception->getCode(),
                $httpCode,
                $exception->getParameters(),
                get_class($exception),
                $errors,
                $stackTrace
            );
        }

        return $proceed($exception);
    }
}
