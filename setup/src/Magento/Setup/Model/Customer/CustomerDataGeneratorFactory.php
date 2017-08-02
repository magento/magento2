<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Customer;

/**
 * Create new instance of CustomerDataGenerator
 * @since 2.2.0
 */
class CustomerDataGeneratorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.2.0
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
     * @since 2.2.0
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
