<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Cron;

/**
 * Class to get PSR-3 compliant logger instance
 * @since 2.1.0
 */
class SetupLoggerFactory
{
    /**
     * Create logger instance.
     *
     * @param string $channelName
     *
     * @return \Psr\Log\LoggerInterface
     * @since 2.1.0
     */
    public function create($channelName = 'setup-cron')
    {
        $logger = new \Monolog\Logger($channelName);
        $path = BP . '/var/log/update.log';
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path));
        return $logger;
    }
}
