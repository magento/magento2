<?php
/**
 * Factory class for \Magento\Framework\Authorization
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Framework\Api\AttributeValueFactory
 *
 * @since 2.0.0
 */
class AttributeValueFactory
{
    /**
     * Entity class name
     */
    const CLASS_NAME = \Magento\Framework\Api\AttributeValue::class;

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @return AttributeValue
     * @since 2.0.0
     */
    public function create()
    {
        return $this->_objectManager->create(self::CLASS_NAME, ['data' => []]);
    }
}
