<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlNewRelic\Plugin;

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
     * Sends error from GraphQL to New Relic
     *
     * @param ErrorHandler $subject
     * @param Error[] $errors
     * @param callable $formatter
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeHandle(ErrorHandler $subject, array $errors, callable $formatter)
    {
        if (!empty($errors)) {
            $error = $errors[0];
            if (($error instanceof Error ) && $error->getPrevious()) {
                $error = $error->getPrevious();
            }
            $this->newRelicWrapper->reportError($error); // Note: We only log the first error because performance
        }
        return null;
    }
}
