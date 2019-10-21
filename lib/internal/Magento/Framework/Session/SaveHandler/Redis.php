<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\SaveHandler;

use Cm\RedisSession\Handler\ConfigInterface;
use Cm\RedisSession\Handler\LoggerInterface;
use Cm\RedisSession\ConnectionFailedException;
use Cm\RedisSession\ConcurrentConnectionsExceededException;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Phrase;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Redis implements \SessionHandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var \Cm\RedisSession\Handler[]
     */
    private $connection;

    /**
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @throws SessionException
     */
    public function __construct(ConfigInterface $config, LoggerInterface $logger, Filesystem $filesystem)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
    }

    /**
     * Get connection
     *
     * @return \Cm\RedisSession\Handler
     * @throws SessionException
     */
    private function getConnection()
    {
        $pid = getmypid();
        if (!isset($this->connection[$pid])) {
            try {
                $this->connection[$pid] = new \Cm\RedisSession\Handler($this->config, $this->logger);
            } catch (ConnectionFailedException $e) {
                throw new SessionException(new Phrase($e->getMessage()));
            }
        }
        return $this->connection[$pid];
    }

    /**
     * Open session
     *
     * @param string $savePath ignored
     * @param string $sessionName ignored
     * @return bool
     * @throws SessionException
     */
    public function open($savePath, $sessionName)
    {
        return $this->getConnection()->open($savePath, $sessionName);
    }

    /**
     * Fetch session data
     *
     * @param string $sessionId
     * @return string
     * @throws ConcurrentConnectionsExceededException
     * @throws SessionException
     */
    public function read($sessionId)
    {
        try {
            return $this->getConnection()->read($sessionId);
        } catch (ConcurrentConnectionsExceededException $e) {
            require $this->filesystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath('errors/503.php');
        }
    }

    /**
     * Update session
     *
     * @param string $sessionId
     * @param string $sessionData
     * @return boolean
     * @throws SessionException
     */
    public function write($sessionId, $sessionData)
    {
        return $this->getConnection()->write($sessionId, $sessionData);
    }

    /**
     * Destroy session
     *
     * @param string $sessionId
     * @return boolean
     * @throws SessionException
     */
    public function destroy($sessionId)
    {
        return $this->getConnection()->destroy($sessionId);
    }

    /**
     * Overridden to prevent calling getLifeTime at shutdown
     *
     * @return bool
     * @throws SessionException
     */
    public function close()
    {
        return $this->getConnection()->close();
    }

    /**
     * Garbage collection
     *
     * @param int $maxLifeTime ignored
     * @return boolean
     * @throws SessionException
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function gc($maxLifeTime)
    {
        return $this->getConnection()->gc($maxLifeTime);
    }

    /**
     * Get the number of failed lock attempts
     *
     * @return int
     * @throws SessionException
     */
    public function getFailedLockAttempts()
    {
        return $this->getConnection()->getFailedLockAttempts();
    }
}
