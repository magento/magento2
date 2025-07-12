<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlNewRelic\Plugin;

use Magento\Framework\GraphQl\Exception\ExceptionFormatter;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

/**
 * Plugin that sends GraphQL Errors to New Relic
 */
class ReportException
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
     * @param ExceptionFormatter $subject
     * @param \Throwable $exception
     * @param string|null $internalErrorMessage
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCheck(
        ExceptionFormatter $subject,
        \Throwable $exception,
        ?string $internalErrorMessage = null
    ) {
        $this->newRelicWrapper->reportError($exception);
        return null;
    }
}
