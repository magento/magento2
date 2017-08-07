<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\SaveHandler\Redis;

use Cm\RedisSession\Handler\ConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Request\Http as Request;

/**
 * Class \Magento\Framework\Session\SaveHandler\Redis\Logger
 *
 * @since 2.0.6
 */
class Logger implements \Cm\RedisSession\Handler\LoggerInterface
{
    /**
     * @var LoggerInterface
     * @since 2.0.6
     */
    private $logger;

    /**
     * @var int
     * @since 2.0.6
     */
    private $logLevel;

    /**
     * @var Request
     * @since 2.0.6
     */
    private $request;

    /**
     * Logger constructor
     *
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param Request $request
     * @since 2.0.6
     */
    public function __construct(ConfigInterface $config, LoggerInterface $logger, Request $request)
    {
        $this->logger = $logger;
        $this->request = $request;
        $this->logLevel = $config->getLogLevel() ?: self::ALERT;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function setLogLevel($level)
    {
        $this->logLevel = $level;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function log($message, $level)
    {
        $message .= ' ' . $this->request->getRequestUri();
        if ($this->logLevel >= $level) {
            switch ($level) {
                case self::EMERGENCY:
                    $this->logger->emergency($message);
                    break;
                case self::ALERT:
                    $this->logger->alert($message);
                    break;
                case self::CRITICAL:
                    $this->logger->critical($message);
                    break;
                case self::ERROR:
                    $this->logger->error($message);
                    break;
                case self::WARNING:
                    $this->logger->warning($message);
                    break;
                case self::NOTICE:
                    $this->logger->notice($message);
                    break;
                case self::INFO:
                    $this->logger->info($message);
                    break;
                default:
                    $this->logger->debug($message);
            }
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function logException(\Exception $e)
    {
        $this->logger->critical($e->getMessage());
    }
}
