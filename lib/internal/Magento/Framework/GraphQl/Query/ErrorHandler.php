<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use Magento\Framework\Exception\AggregateExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritDoc
 *
 * @package Magento\Framework\GraphQl\Query
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $errors, callable $formatter): array
    {
        $formattedErrors = [];
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
