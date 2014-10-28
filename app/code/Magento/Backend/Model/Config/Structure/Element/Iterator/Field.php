<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Config\Structure\Element\Iterator;

class Field extends \Magento\Backend\Model\Config\Structure\Element\Iterator
{
    /**
     * Group flyweight
     *
     * @var \Magento\Backend\Model\Config\Structure\Element\Group
     */
    protected $_groupFlyweight;

    /**
     * Field element flyweight
     *
     * @var \Magento\Backend\Model\Config\Structure\Element\Field
     */
    protected $_fieldFlyweight;

    /**
     * @param \Magento\Backend\Model\Config\Structure\Element\Group $groupFlyweight
     * @param \Magento\Backend\Model\Config\Structure\Element\Field $fieldFlyweight
     */
    public function __construct(
        \Magento\Backend\Model\Config\Structure\Element\Group $groupFlyweight,
        \Magento\Backend\Model\Config\Structure\Element\Field $fieldFlyweight
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
     */
    protected function _initFlyweight(array $element)
    {
        if (!isset($element[\Magento\Backend\Model\Config\Structure::TYPE_KEY])) {
            throw new \LogicException('System config structure element must contain "type" attribute');
        }
        switch ($element[\Magento\Backend\Model\Config\Structure::TYPE_KEY]) {
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
