<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;

class CalculatorFactory
{
    /**
     * Identifier constant for unit based calculation
     */
    const CALC_UNIT_BASE = 'UNIT_BASE_CALCULATION';

    /**
     * Identifier constant for row based calculation
     */
    const CALC_ROW_BASE = 'ROW_BASE_CALCULATION';

    /**
     * Identifier constant for total based calculation
     */
    const CALC_TOTAL_BASE = 'TOTAL_BASE_CALCULATION';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create new calculator
     *
     * @param string $type Type of calculator
     * @param int $storeId
     * @param CustomerAddress $billingAddress
     * @param CustomerAddress $shippingAddress
     * @param null|int $customerTaxClassId
     * @param null|int $customerId
     * @return \Magento\Tax\Model\Calculation\AbstractCalculator
     * @throws \InvalidArgumentException
     */
    public function create(
        $type,
        $storeId,
        CustomerAddress $billingAddress = null,
        CustomerAddress $shippingAddress = null,
        $customerTaxClassId = null,
        $customerId = null
    ) {
        switch ($type) {
            case self::CALC_UNIT_BASE:
                $className = 'Magento\Tax\Model\Calculation\UnitBaseCalculator';
                break;
            case self::CALC_ROW_BASE:
                $className = 'Magento\Tax\Model\Calculation\RowBaseCalculator';
                break;
            case self::CALC_TOTAL_BASE:
                $className = 'Magento\Tax\Model\Calculation\TotalBaseCalculator';
                break;
            default:
                throw new \InvalidArgumentException('Unknown calculation type: ' . $type);
        }
        /** @var \Magento\Tax\Model\Calculation\AbstractCalculator $calculator */
        $calculator = $this->objectManager->create($className, ['storeId' => $storeId]);
        if (null != $shippingAddress) {
            $calculator->setShippingAddress($shippingAddress);
        }
        if (null != $billingAddress) {
            $calculator->setBillingAddress($billingAddress);
        }
        if (null != $customerTaxClassId) {
            $calculator->setCustomerTaxClassId($customerTaxClassId);
        }
        if (null != $customerId) {
            $calculator->setCustomerId($customerId);
        }
        return $calculator;
    }
}
