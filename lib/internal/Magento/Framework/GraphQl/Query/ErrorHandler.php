<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Error\Error;
use Magento\Framework\App\State;
use Magento\Framework\Exception\AggregateExceptionInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Psr\Log\LoggerInterface;

/**
 * @inheritDoc
 */
class ErrorHandler implements ErrorHandlerInterface
{
    private const SERVER_ERROR_CATEGORY = 'graphql-server';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var State
     */
    private $appState;

    /**
     * @param LoggerInterface $logger
     * @param State $appState
     */
    public function __construct(
        LoggerInterface $logger,
        State $appState
    ) {
        $this->logger = $logger;
        $this->appState = $appState;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $errors, callable $formatter): array
    {
        $formattedErrors = [];
        $loggableErrors = array_values(
            array_filter($errors, fn (Error $error): bool => !$this->isClientError($error))
        );

        // When not in developer mode, only log & report the first error for performance implications
        if ($this->appState->getMode() !== State::MODE_DEVELOPER) {
            $errors = array_splice($errors, 0, 1);
            $loggableErrors = array_splice($loggableErrors, 0, 1);
        }

        foreach ($loggableErrors as $error) {
            $this->logger->error($error);
            foreach ($this->getAggregatedErrors($error) as $aggregatedError) {
                $this->logger->error($aggregatedError);
            }
        }

        foreach ($errors as $error) {
            $aggregatedErrors = $this->getAggregatedErrors($error);
            if (!empty($aggregatedErrors)) {
                foreach ($aggregatedErrors as $aggregatedError) {
                    $formattedErrors[] = $formatter($aggregatedError);
                }
            } else {
                $formattedErrors[] = $formatter($error);
            }
        }
        return $formattedErrors;
    }

    /**
     * Check whether the error was caused by the client request and therefore must not be logged.
     *
     * @param Error $error
     * @return bool
     */
    private function isClientError(Error $error): bool
    {
        $category = $error->getExtensions()['category'] ?? null;
        if (GraphQlInputException::EXCEPTION_CATEGORY === $category) {
            return true;
        }

        // GraphQlServerException is client-safe so its message reaches the client, but it reports a server failure
        return $error->isClientSafe() && self::SERVER_ERROR_CATEGORY !== $category;
    }

    /**
     * Get the child errors of an aggregate exception wrapped by the error.
     *
     * @param Error $error
     * @return array
     */
    private function getAggregatedErrors(Error $error): array
    {
        $previousError = $error->getPrevious();

        return $previousError instanceof AggregateExceptionInterface ? $previousError->getErrors() : [];
    }
}
