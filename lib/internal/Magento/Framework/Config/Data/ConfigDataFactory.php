<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Data;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

/**
<<<<<<< HEAD
 * Factory for ConfigData
=======
 * Factory for ConfigData.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class ConfigDataFactory
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
<<<<<<< HEAD
     * Factory constructor
=======
     * Factory constructor.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
