<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Cron;

/**
 * Class to get PSR-3 compliant logger instance
 */
class SetupLoggerFactory
{
    /**
     * Create logger instance.
     *
     * @param string $channelName
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function create($channelName = 'setup-cron')
    {
        $logger = new \Monolog\Logger($channelName);
        $path = BP . '/var/log/update.log';
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path));
        return $logger;
    }
}
