<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

use InvalidArgumentException;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Framework\ObjectManagerInterface;

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
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager
    ) {
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
     * @return AbstractCalculator
     * @throws InvalidArgumentException
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
                $className = UnitBaseCalculator::class;
                break;
            case self::CALC_ROW_BASE:
                $className = RowBaseCalculator::class;
                break;
            case self::CALC_TOTAL_BASE:
                $className = TotalBaseCalculator::class;
                break;
            default:
                throw new InvalidArgumentException('Unknown calculation type: ' . $type);
        }
        /** @var AbstractCalculator $calculator */
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
