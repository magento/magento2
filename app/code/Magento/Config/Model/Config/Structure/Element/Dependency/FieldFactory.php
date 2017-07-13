<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element\Dependency;

/**
 * @api
 */
class FieldFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create dependency field model instance.
     *
     * @param array $arguments
     * @return Field
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(
            \Magento\Config\Model\Config\Structure\Element\Dependency\Field::class,
            $arguments
        );
    }
}
