<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Exception\SessionException;

/**
 * Magento session save handler
 */
class SaveHandler implements SaveHandlerInterface
{
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
     * @param SaveHandlerFactory $saveHandlerFactory
     * @param ConfigInterface $sessionConfig
     * @param string $default
     */
    public function __construct(
        SaveHandlerFactory $saveHandlerFactory,
        ConfigInterface $sessionConfig,
        $default = self::DEFAULT_HANDLER
    ) {
        $this->saveHandlerFactory = $saveHandlerFactory;
        $this->sessionConfig = $sessionConfig;
        $this->defaultHandler = $default;

        /**
         * Session handler
         *
         * Save handler may be set to custom value in deployment config, which will override everything else.
         * Otherwise, try to read PHP settings for session.save_handler value. Otherwise, use 'files' as default.
         */
        $saveMethod = $this->sessionConfig->getOption('session.save_handler') ?: $this->defaultHandler;

        try {
            $this->saveHandlerAdapter = $this->saveHandlerFactory->create($saveMethod);
        } catch (SessionException $e) {
            $this->saveHandlerAdapter = $this->saveHandlerFactory->create($this->defaultHandler);
        }
    }

    /**
     * Open Session - retrieve resources
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
     * Close Session - free resources
     *
     * @return bool
     */
    public function close()
    {
        return $this->callSafely('close');
    }

    /**
     * Read session data
     *
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        return $this->callSafely('read', $sessionId);
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $sessionId
     * @param string $data
     * @return bool
     */
    public function write($sessionId, $data)
    {
        return $this->callSafely('write', $sessionId, $data);
    }

    /**
     * Destroy Session - remove data from resource for given session id
     *
     * @param string $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        return $this->callSafely('destroy', $sessionId);
    }

    /**
     * Garbage Collection - remove old session data older than $maxLifetime (in seconds)
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
            return $this->saveHandlerAdapter->{$method}(...$arguments);
        } catch (SessionException $exception) {
            $this->saveHandlerAdapter = $this->saveHandlerFactory->create($this->defaultHandler);
            return $this->saveHandlerAdapter->{$method}(...$arguments);
        }
    }
}
