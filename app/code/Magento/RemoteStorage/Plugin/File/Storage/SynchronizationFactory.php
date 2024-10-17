<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin\File\Storage;

use Magento\RemoteStorage\Model\File\Storage\Synchronization;
use Magento\MediaStorage\Model\File\Storage\SynchronizationFactory as MediaSynchronizationFactory;
use Magento\RemoteStorage\Model\Config;
use Magento\Framework\ObjectManagerInterface;

/**
 * This is a plugin to Magento\MediaStorage\Model\File\Storage\SynchronizationFactory.
 */
class SynchronizationFactory
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
     * Create remote synchronization instance if remote storage is enabled.
     * Otherwise, defer creation to MediaSynchronizationFactory
     *
     * @param MediaSynchronizationFactory $subject
     * @param callable $proceed
     * @param array $data
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCreate(MediaSynchronizationFactory $subject, callable $proceed, array $data = [])
    {
        if ($this->config->isEnabled()) {
            return $this->objectManager->create(Synchronization::class, $data);
        }
        return $proceed($data);
    }
}
