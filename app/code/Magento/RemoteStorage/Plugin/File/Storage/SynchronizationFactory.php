<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin\File\Storage;

use Magento\RemoteStorage\Model\File\Storage\Synchronization;
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
     * Create remote synchronization instance
     * @param Synchronization $subject
     * @param array $data
     */
    public function beforeCreate(Synchronization $subject, array $data = [])
    {
        if ($this->config->isEnabled()) {
            return $this->objectManager->create(Synchronization::class, $data);
        }
    }
}
