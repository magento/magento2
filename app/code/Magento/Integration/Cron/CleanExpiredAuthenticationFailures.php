<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Integration\Cron;

use Magento\Integration\Model\Oauth\Token\RequestLog\WriterInterface as RequestLogWriter;

/**
 * Cron class for clearing log of outdated token request authentication failures.
 */
class CleanExpiredAuthenticationFailures
{
    /**
     * @var RequestLogWriter
     */
    private $requestLogWriter;

    /**
     * Initialize dependencies.
     *
     * @param RequestLogWriter $requestLogWriter
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
     */
    public function execute()
    {
        $this->requestLogWriter->clearExpiredFailures();
    }
}
