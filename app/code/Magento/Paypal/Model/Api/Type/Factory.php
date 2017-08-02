<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Factory class for \Magento\Paypal\Model\Api\AbstractApi
 */
namespace Magento\Paypal\Model\Api\Type;

/**
 * Class \Magento\Paypal\Model\Api\Type\Factory
 *
 * @since 2.0.0
 */
class Factory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $className
     * @param array $data
     * @return \Magento\Paypal\Model\Api\AbstractApi
     * @since 2.0.0
     */
    public function create($className, array $data = [])
    {
        return $this->_objectManager->create($className, $data);
    }
}
