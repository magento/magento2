<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Structure\Element;

class FlyweightFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Map of flyweight types
     *
     * @var array
     */
    protected $_flyweightMap = [
        'section' => 'Magento\Backend\Model\Config\Structure\Element\Section',
        'group' => 'Magento\Backend\Model\Config\Structure\Element\Group',
        'field' => 'Magento\Backend\Model\Config\Structure\Element\Field',
    ];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create element flyweight flyweight
     *
     * @param string $type
     * @return \Magento\Backend\Model\Config\Structure\ElementInterface
     */
    public function create($type)
    {
        return $this->_objectManager->create($this->_flyweightMap[$type]);
    }
}
