<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

class CalculatorFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var array
     */
    protected $classByType = [
        \Magento\SalesRule\Model\Rule::TO_PERCENT_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\ToPercent',
        \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\ByPercent',
        \Magento\SalesRule\Model\Rule::TO_FIXED_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\ToFixed',
        \Magento\SalesRule\Model\Rule::BY_FIXED_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\ByFixed',
        \Magento\SalesRule\Model\Rule::CART_FIXED_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\CartFixed',
        \Magento\SalesRule\Model\Rule::BUY_X_GET_Y_ACTION => 'Magento\SalesRule\Model\Rule\Action\Discount\BuyXGetY',
    ];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $discountRules
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $discountRules = [])
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
