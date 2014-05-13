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
namespace Magento\SalesRule\Model\Rule\Action\Discount;

class CalculatorFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    private $_objectManager;

    /**
     * @var array
     */
    protected $classByType = array(
        \Magento\SalesRule\Model\Rule::TO_PERCENT_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\ToPercent',
        \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\ByPercent',
        \Magento\SalesRule\Model\Rule::TO_FIXED_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\ToFixed',
        \Magento\SalesRule\Model\Rule::BY_FIXED_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\ByFixed',
        \Magento\SalesRule\Model\Rule::CART_FIXED_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\CartFixed',
        \Magento\SalesRule\Model\Rule::BUY_X_GET_Y_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\BuyXGetY'
    );

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param array $discountRules
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager, array $discountRules = array())
    {
        $this->classByType = array_merge($this->classByType, $discountRules);
        $this->_objectManager = $objectManager;
    }

    /**
     * @param string $type
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\DiscountInterface
     * @throws \InvalidArgumentException
     */
    public function create($type)
    {
        if (!isset($this->classByType[$type])) {
            throw new \InvalidArgumentException($type . ' is unknown type');
        }

        return $this->_objectManager->create($this->classByType[$type]);
    }
}
