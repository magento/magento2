<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

use Magento\Framework\App\DeploymentConfig;

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
     * Constructor
     *
     * @param SaveHandlerFactory $saveHandlerFactory
     * @param DeploymentConfig $deploymentConfig
     * @param string $default
     */
    public function __construct(
        SaveHandlerFactory $saveHandlerFactory,
        DeploymentConfig $deploymentConfig,
        $default = self::DEFAULT_HANDLER
    ) {
        $saveMethod = $deploymentConfig->get(\Magento\Framework\Session\Config::PARAM_SESSION_SAVE_METHOD);
        try {
            $adapter = $saveHandlerFactory->create($saveMethod);
        } catch (SaveHandlerException $e) {
            $adapter = $saveHandlerFactory->create($default);
        }
        $this->saveHandlerAdapter = $adapter;
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
        return $this->saveHandlerAdapter->open($savePath, $name);
    }

    /**
     * Close Session - free resources
     *
     * @return bool
     */
    public function close()
    {
        return $this->saveHandlerAdapter->close();
    }

    /**
     * Read session data
     *
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        return $this->saveHandlerAdapter->read($sessionId);
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
        return $this->saveHandlerAdapter->write($sessionId, $data);
    }

    /**
     * Destroy Session - remove data from resource for given session id
     *
     * @param string $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        return $this->saveHandlerAdapter->destroy($sessionId);
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxLifetime (in seconds)
     *
     * @param int $maxLifetime
     * @return bool
     */
    public function gc($maxLifetime)
    {
        return $this->saveHandlerAdapter->gc($maxLifetime);
    }
}
