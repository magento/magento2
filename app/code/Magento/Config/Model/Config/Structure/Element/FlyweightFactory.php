<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element;

/**
 * @api
 * @since 2.0.0
 */
class FlyweightFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Map of flyweight types
     *
     * @var array
     * @since 2.0.0
     */
    protected $_flyweightMap = [
        'section' => \Magento\Config\Model\Config\Structure\Element\Section::class,
        'group' => \Magento\Config\Model\Config\Structure\Element\Group::class,
        'field' => \Magento\Config\Model\Config\Structure\Element\Field::class,
    ];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create element flyweight flyweight
     *
     * @param string $type
     * @return \Magento\Config\Model\Config\Structure\ElementInterface
     * @since 2.0.0
     */
    public function create($type)
    {
        return $this->_objectManager->create($this->_flyweightMap[$type]);
    }
}
