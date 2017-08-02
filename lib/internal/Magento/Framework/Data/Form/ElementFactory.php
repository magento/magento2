<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form;

/**
 * Class \Magento\Framework\Data\Form\ElementFactory
 *
 * @since 2.0.0
 */
class ElementFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create Magento data form with provided params
     *
     * @param string $elementClass
     * @param array $data
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     * @since 2.0.0
     */
    public function create($elementClass, array $data = [])
    {
        return $this->_objectManager->create($elementClass, ['data' => $data]);
    }
}
