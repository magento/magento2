<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form;

class ElementFactory
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
     * Create Magento data form with provided params
     *
     * @param string $elementClass
     * @param array $data
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function create($elementClass, array $data = [])
    {
        return $this->_objectManager->create($elementClass, ['data' => $data]);
    }
}
