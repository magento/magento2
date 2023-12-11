<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Logger;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\ObjectManager\NoninterceptableInterface;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Create and use Logger implementation based on deployment configuration
 */
class LoggerProxy implements LoggerInterface, NoninterceptableInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Proxy constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Remove links to other objects.
     *
     * @return array
     */
    public function __sleep()
    {
        return [];
    }

    /**
     * Retrieve ObjectManager from global scope
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * Clone instance
     *
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function __clone()
    {
        $this->logger = clone $this->getLogger();
    }

    /**
     * Get Logger instance
     *
     * @return LoggerInterface
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function getLogger(): LoggerInterface
    {
        if (!$this->logger) {
            $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
            $instanceName = $deploymentConfig->get('log/type') ?? Monolog::class;
            $args = $deploymentConfig->get('log/args');

            if ($args) {
                $this->logger = $this->objectManager->create($instanceName, $args);
            } else {
                $this->logger = $this->objectManager->get($instanceName);
            }
        }
        return $this->logger;
    }

    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = [])
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->emergency($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function alert($message, array $context = [])
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->alert($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = [])
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->critical($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = [])
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->error($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = [])
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->warning($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function notice($message, array $context = [])
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->notice($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = [])
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->info($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = [])
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->debug($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = [])
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->log($level, $message, $context);
    }

    /**
     * Ensure exception logging by adding it to context
     *
     * @param mixed $message
     * @param array $context
     * @return array
     */
    protected function addExceptionToContext($message, array $context = []): array
    {
        if ($message instanceof \Throwable && !isset($context['exception'])) {
            $context['exception'] = $message;
        }
        return $context;
    }
}
