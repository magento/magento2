<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use Magento\Framework\App\State;
use Magento\Framework\Exception\AggregateExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritDoc
 */
class ErrorHandler implements ErrorHandlerInterface
{
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

        // When not in developer mode, only log & report the first error for performance implications
        if ($this->appState->getMode() !== State::MODE_DEVELOPER) {
            $errors = array_splice($errors, 0, 1);
        }

        foreach ($errors as $error) {
            $this->logger->error($error);
            $previousError = $error->getPrevious();
            if ($previousError instanceof AggregateExceptionInterface && !empty($previousError->getErrors())) {
                $aggregatedErrors = $previousError->getErrors();
                foreach ($aggregatedErrors as $aggregatedError) {
                    $this->logger->error($aggregatedError);
                    $formattedErrors[] = $formatter($aggregatedError);
                }
            } else {
                $formattedErrors[] = $formatter($error);
            }
        }
        return $formattedErrors;
    }
}
