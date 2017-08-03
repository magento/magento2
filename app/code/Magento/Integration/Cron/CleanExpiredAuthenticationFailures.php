<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Cron;

use Magento\Integration\Model\Oauth\Token\RequestLog\WriterInterface as RequestLogWriter;

/**
 * Cron class for clearing log of outdated token request authentication failures.
 * @since 2.0.3
 */
class CleanExpiredAuthenticationFailures
{
    /**
     * @var RequestLogWriter
     * @since 2.0.3
     */
    private $requestLogWriter;

    /**
     * Initialize dependencies.
     *
     * @param RequestLogWriter $requestLogWriter
     * @since 2.0.3
     */
    public function __construct(
        RequestLogWriter $requestLogWriter
    ) {
        $this->requestLogWriter = $requestLogWriter;
    }

    /**
     * Clearing log of outdated token request authentication failures.
     *
     * @return void
     * @since 2.0.3
     */
    public function execute()
    {
        $this->requestLogWriter->clearExpiredFailures();
    }
}
