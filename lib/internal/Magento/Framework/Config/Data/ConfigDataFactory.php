<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Data;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for ConfigData
 */
class ConfigDataFactory
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Returns a new instance of ConfigData on every call.
     *
     * @param string $fileKey
     * @return ConfigData
     */
    public function create($fileKey)
    {
        return $this->objectManager->create(ConfigData::class, ['fileKey' => $fileKey]);
    }
}
