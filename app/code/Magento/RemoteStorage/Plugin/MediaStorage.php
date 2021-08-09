<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin;

use Magento\RemoteStorage\Model\File\Storage\Synchronization;
use Magento\RemoteStorage\Model\Config;
use Magento\Framework\ObjectManagerInterface;

/**
 * Helps to synchronize files from remote to local file system
 */
class MediaStorage
{
     /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var Config
     */
    private $config;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     */
    public function aroundCreate(array $data = [])
    {
        if ($this->config->isEnabled()) {
            return $this->objectManager->create(Synchronization::class, $data);
        }
    }
}
