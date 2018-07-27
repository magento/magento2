<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Customer;

/**
 * Create new instance of CustomerDataGenerator
 */
class CustomerDataGeneratorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create CustomerGenerator instance with specified configuration
     *
     * @param array $config
     * @return \Magento\Setup\Model\Customer\CustomerDataGenerator
     */
    public function create(array $config)
    {
        return $this->objectManager->create(
            \Magento\Setup\Model\Customer\CustomerDataGenerator::class,
            [
                'addressGenerator' => $this->objectManager->create(
                    \Magento\Setup\Model\Address\AddressDataGenerator::class
                ),
                'config' => $config
            ]
        );
    }
}
