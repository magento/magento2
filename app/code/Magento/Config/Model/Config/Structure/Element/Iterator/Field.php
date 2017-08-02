<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element\Iterator;

/**
 * @api
 * @since 2.0.0
 */
class Field extends \Magento\Config\Model\Config\Structure\Element\Iterator
{
    /**
     * Group flyweight
     *
     * @var \Magento\Config\Model\Config\Structure\Element\Group
     * @since 2.0.0
     */
    protected $_groupFlyweight;

    /**
     * Field element flyweight
     *
     * @var \Magento\Config\Model\Config\Structure\Element\Field
     * @since 2.0.0
     */
    protected $_fieldFlyweight;

    /**
     * @param \Magento\Config\Model\Config\Structure\Element\Group $groupFlyweight
     * @param \Magento\Config\Model\Config\Structure\Element\Field $fieldFlyweight
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Config\Model\Config\Structure\Element\Group $groupFlyweight,
        \Magento\Config\Model\Config\Structure\Element\Field $fieldFlyweight
    ) {
        $this->_groupFlyweight = $groupFlyweight;
        $this->_fieldFlyweight = $fieldFlyweight;
    }

    /**
     * Init current element
     *
     * @param array $element
     * @return void
     * @throws \LogicException
     * @since 2.0.0
     */
    protected function _initFlyweight(array $element)
    {
        if (!isset($element[\Magento\Config\Model\Config\Structure::TYPE_KEY])) {
            throw new \LogicException('System config structure element must contain "type" attribute');
        }
        switch ($element[\Magento\Config\Model\Config\Structure::TYPE_KEY]) {
            case 'group':
                $this->_flyweight = $this->_groupFlyweight;
                break;

            case 'field':
            default:
                $this->_flyweight = $this->_fieldFlyweight;
        }
        parent::_initFlyweight($element);
    }
}
