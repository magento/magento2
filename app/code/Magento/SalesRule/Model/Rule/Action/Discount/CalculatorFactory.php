<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule;

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
        Rule::TO_PERCENT_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\ToPercent::class,
        Rule::BY_PERCENT_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\ByPercent::class,
        Rule::TO_FIXED_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\ToFixed::class,
        Rule::BY_FIXED_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\ByFixed::class,
        Rule::CART_FIXED_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed::class,
        Rule::BUY_X_GET_Y_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\BuyXGetY::class,
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
