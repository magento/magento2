<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlNewRelic\Plugin;

use GraphQL\Error\ClientAware;
use GraphQL\Error\Error;
use Magento\Framework\GraphQl\Query\ErrorHandler;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

/**
 * Plugin that sends GraphQL Errors to New Relic
 */
class ReportError
{
    /**
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(private NewRelicWrapper $newRelicWrapper)
    {
    }

    /**
     * Sends the first GraphQL error that is not client-safe to New Relic
     *
     * @param ErrorHandler $subject
     * @param Error[] $errors
     * @param callable $formatter
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeHandle(ErrorHandler $subject, array $errors, callable $formatter)
    {
        foreach ($errors as $error) {
            if ($error instanceof ClientAware && $error->isClientSafe()) {
                continue;
            }
            if (($error instanceof Error) && $error->getPrevious()) {
                $error = $error->getPrevious();
            }
            $this->newRelicWrapper->reportError($error);
            break;
        }
        return null;
    }
}
