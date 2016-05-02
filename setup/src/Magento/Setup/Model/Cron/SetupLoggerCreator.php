<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Cron;

use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Class to get PSR-3 compliant logger instance
 */
class SetupLoggerCreator
{
    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var SetupStreamHandler
     */
    private $setupStreamHandler;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param SetupStreamHandler $setupStreamHandler
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        SetupStreamHandler $setupStreamHandler
    ) {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->setupStreamHandler = $setupStreamHandler;
    }

    /**
     * Create logger instance.
     *
     * @param string $channelName
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function create($channelName)
    {
        /** @var \Magento\Framework\Logger\Monolog $logger */
        $logger = $this->objectManagerProvider
            ->get()
            ->create('Magento\Framework\Logger\Monolog', ['name' => $channelName]);
        $logger->pushHandler($this->setupStreamHandler);
        return $logger;
    }
}
