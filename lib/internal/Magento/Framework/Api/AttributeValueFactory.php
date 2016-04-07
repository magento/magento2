<?php
/**
 * Factory class for \Magento\Framework\Authorization
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

use Magento\Framework\ObjectManagerInterface;

class AttributeValueFactory
{
    /**
     * Entity class name
     */
    const CLASS_NAME = 'Magento\Framework\Api\AttributeValue';

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @return AttributeValue
     */
    public function create()
    {
        return $this->_objectManager->create(self::CLASS_NAME, ['data' => []]);
    }
}
