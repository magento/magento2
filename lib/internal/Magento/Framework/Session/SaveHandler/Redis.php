<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

class Redis extends \Cm\RedisSession\Handler
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @throws SessionException
     */
    public function __construct(ConfigInterface $config, LoggerInterface $logger, Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        try {
            parent::__construct($config, $logger);
        } catch (ConnectionFailedException $e) {
            throw new SessionException(new Phrase($e->getMessage()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        try {
            return parent::read($sessionId);
        } catch (ConcurrentConnectionsExceededException $e) {
            require $this->filesystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath('errors/503.php');
        }
    }
}
