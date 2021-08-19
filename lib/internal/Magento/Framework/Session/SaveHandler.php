<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Session;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Session\Config\ConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Magento session save handler.
 */
class SaveHandler implements SaveHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Session handler
     *
     * @var \SessionHandler
     */
    protected $saveHandlerAdapter;

    /**
     * @var SaveHandlerFactory
     */
    private $saveHandlerFactory;

    /**
     * @var ConfigInterface
     */
    private $sessionConfig;

    /**
     * @var string
     */
    private $defaultHandler;

    /**
     * @var SessionMaxSizeConfig
     */
    private $sessionMaxSizeConfig;

    /**
     * @param SaveHandlerFactory $saveHandlerFactory
     * @param ConfigInterface $sessionConfig
     * @param LoggerInterface $logger
     * @param SessionMaxSizeConfig $sessionMaxSizeConfigs
     * @param string $default
     */
    public function __construct(
        SaveHandlerFactory $saveHandlerFactory,
        ConfigInterface $sessionConfig,
        LoggerInterface $logger,
        SessionMaxSizeConfig $sessionMaxSizeConfigs,
        $default = self::DEFAULT_HANDLER
    ) {
        $this->saveHandlerFactory = $saveHandlerFactory;
        $this->sessionConfig = $sessionConfig;
        $this->logger = $logger;
        $this->defaultHandler = $default;
        $this->sessionMaxSizeConfig = $sessionMaxSizeConfigs;
    }

    /**
     * Open Session - retrieve resources.
     *
     * @param string $savePath
     * @param string $name
     * @return bool
     */
    public function open($savePath, $name)
    {
        return $this->callSafely('open', $savePath, $name);
    }

    /**
     * Close Session - free resources.
     *
     * @return bool
     */
    public function close()
    {
        return $this->callSafely('close');
    }

    /**
     * Read session data.
     *
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        return $this->callSafely('read', $sessionId);
    }

    /**
     * Write Session - commit data to resource.
     *
     * @param string $sessionId
     * @param string $data
     * @return bool
     * @throws LocalizedException
     */
    public function write($sessionId, $data)
    {
        $sessionMaxSize = $this->sessionMaxSizeConfig->getSessionMaxSize();
        $sessionSize = strlen($data);

        if ($sessionMaxSize === null || $sessionMaxSize >= $sessionSize) {
            return $this->callSafely('write', $sessionId, $data);
        }

        $this->logger->warning(
            sprintf(
                'Session size of %d exceeded allowed session max size of %d.',
                $sessionSize,
                $sessionMaxSize
            )
        );

        return $this->callSafely('write', $sessionId, $this->read($sessionId));
    }

    /**
     * Destroy Session - remove data from resource for given session id.
     *
     * @param string $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        return $this->callSafely('destroy', $sessionId);
    }

    /**
     * Garbage Collection - remove old session data older than $maxLifetime (in seconds).
     *
     * @param int $maxLifetime
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function gc($maxLifetime)
    {
        return $this->callSafely('gc', $maxLifetime);
    }

    /**
     * Call save handler adapter method.
     *
     * In case custom handler failed, default files handler is used.
     *
     * @param string $method
     * @param mixed $arguments
     *
     * @return mixed
     */
    private function callSafely(string $method, ...$arguments)
    {
        try {
            if ($this->saveHandlerAdapter === null) {
                $saveMethod = $this->sessionConfig->getOption('session.save_handler') ?: $this->defaultHandler;
                $this->saveHandlerAdapter = $this->saveHandlerFactory->create($saveMethod);
            }
            return $this->saveHandlerAdapter->{$method}(...$arguments);
        } catch (SessionException $exception) {
            $this->saveHandlerAdapter = $this->saveHandlerFactory->create($this->defaultHandler);
            return $this->saveHandlerAdapter->{$method}(...$arguments);
        }
    }
}
