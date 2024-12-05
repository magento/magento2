<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Exception;

use Exception;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\FormattedError;
use Magento\Framework\App\State;
use Magento\Framework\Webapi\ErrorProcessor;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Wrapper for GraphQl Exception Formatter
 */
class ExceptionFormatter
{
    public const HTTP_GRAPH_QL_SCHEMA_ERROR_STATUS = 500;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param State $appState
     * @param ErrorProcessor $errorProcessor
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(State $appState, ErrorProcessor $errorProcessor, LoggerInterface $logger)
    {
        $this->appState = $appState;
        $this->logger = $logger;
    }

    /**
     * Format a GraphQL error from an exception by converting it to array to conform to GraphQL spec.
     *
     * This method only exposes exception message when exception implements ClientAware interface
     * (or when debug flags are passed).
     *
     * @param Throwable $exception
     * @param string|null $internalErrorMessage
     * @return array
     * @throws Throwable
     */
    public function create(Throwable $exception, $internalErrorMessage = null): array
    {
        if (!$this->shouldShowDetail()) {
            $reportId = uniqid("graph-ql-");
            $message = "Report ID: {$reportId}; Message: {$exception->getMessage()}";
            $code = $exception->getCode();
            $loggedException = new Exception($message, $code, $exception);
            $this->logger->critical($loggedException);
        }

        return FormattedError::createFromException(
            $exception,
            (int) ($this->shouldShowDetail() ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE : false),
            $internalErrorMessage
        );
    }

    /**
     * Return true if detailed error message should be displayed to client, false otherwise.
     *
     * @return bool
     */
    public function shouldShowDetail(): bool
    {
        return $this->appState->getMode() === State::MODE_DEVELOPER;
    }
}
