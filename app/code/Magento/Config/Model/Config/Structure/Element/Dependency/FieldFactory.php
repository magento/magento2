<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Config\Model\Config\Structure\Element\Dependency;

/**
 * @api
 * @since 100.0.2
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
