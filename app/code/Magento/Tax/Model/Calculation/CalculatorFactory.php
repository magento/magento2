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

namespace Magento\Tax\Model\Calculation;

use \Magento\Customer\Service\V1\Data\Address;

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
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create new calculator
     *
     * @param string $type Type of calculator
     * @param int $storeId
     * @param Address $billingAddress
     * @param Address $shippingAddress
     * @param null|int $customerTaxClassId
     * @param null|int $customerId
     * @return \Magento\Tax\Model\Calculation\AbstractCalculator
     * @throws \InvalidArgumentException
     */
    public function create(
        $type,
        $storeId,
        Address $billingAddress = null,
        Address $shippingAddress = null,
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
