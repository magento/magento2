<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method;

use Psr\Log\LoggerInterface;

/**
 * Class Logger for payment related information (request, response, etc.) which is used for debug
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Logger
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs payment related information used for debug
     *
     * @param mixed $logData
     * @param ConfigInterface $config
     *
     * @return void
     */
    public function debug($logData, ConfigInterface $config)
    {
        if ($config->getConfigValue('debug')) {
            $this->logger->debug(var_export($logData, true));
        }
    }
}

