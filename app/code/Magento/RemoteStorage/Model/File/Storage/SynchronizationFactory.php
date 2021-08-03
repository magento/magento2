<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model\File\Storage;

use Magento\Framework\ObjectManagerInterface;
use Magento\RemoteStorage\Model\Config;

/**
 * Factory class for @see \Magento\RemoteStorage\Model\File\Storage\Synchronization
 */
class SynchronizationFactory extends \Magento\MediaStorage\Model\File\Storage\SynchronizationFactory
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
        $this->config = $config;
        parent::__construct($objectManager);
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\MediaStorage\Model\File\Storage\Synchronization|mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function create(array $data = [])
    {
        if ($this->config->isEnabled()) {
            return $this->objectManager->create(Synchronization::class, $data);
        }
        return parent::create($data);
    }
}
