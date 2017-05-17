<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Session\Config\ConfigInterface;

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
     * Config
     *
     * @var ConfigInterface
     */
    private $config;

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
        /**
         * Session handler
         *
         * Save handler may be set to custom value in deployment config, which will override everything else.
         * Otherwise, try to read PHP settings for session.save_handler value. Otherwise, use 'files' as default.
         */
        $defaultSaveHandler = ini_get('session.save_handler') ?: SaveHandlerInterface::DEFAULT_HANDLER;
        $saveMethod = $deploymentConfig->get(Config::PARAM_SESSION_SAVE_METHOD, $defaultSaveHandler);
        $this->setSaveHandler($saveMethod);

        try {
            $connection = $saveHandlerFactory->create($saveMethod);
        } catch (SessionException $e) {
            $connection = $saveHandlerFactory->create($default);
            $this->setSaveHandler($default);
        }
        $this->saveHandlerAdapter = $connection;
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
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function gc($maxLifetime)
    {
        return $this->saveHandlerAdapter->gc($maxLifetime);
    }

    /**
     * Get config
     *
     * @return ConfigInterface
     * @deprecated
     */
    private function getConfig()
    {
        if ($this->config === null) {
            $this->config = ObjectManager::getInstance()->get(ConfigInterface::class);
        }
        return $this->config;
    }

    /**
     * Set session.save_handler option
     *
     * @param string $saveHandler
     * @return $this
     */
    private function setSaveHandler($saveHandler)
    {
        if ($saveHandler === 'db' || $saveHandler === 'redis') {
            $saveHandler = 'user';
        }
        $this->getConfig()->setOption('session.save_handler', $saveHandler);
        return $this;
    }
}
