<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

/**
 * Class \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory
 *
 * @since 2.0.0
 */
class CalculatorFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    private $_objectManager;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $classByType = [
        \Magento\SalesRule\Model\Rule::TO_PERCENT_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\ToPercent::class,
        \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\ByPercent::class,
        \Magento\SalesRule\Model\Rule::TO_FIXED_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\ToFixed::class,
        \Magento\SalesRule\Model\Rule::BY_FIXED_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\ByFixed::class,
        \Magento\SalesRule\Model\Rule::CART_FIXED_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed::class,
        \Magento\SalesRule\Model\Rule::BUY_X_GET_Y_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\BuyXGetY::class,
    ];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $discountRules
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create($type)
    {
        if (!isset($this->classByType[$type])) {
            throw new \InvalidArgumentException($type . ' is unknown type');
        }

        return $this->_objectManager->create($this->classByType[$type]);
    }
}
