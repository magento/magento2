<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Data;

use Magento\SalesRule\Api\Data\DiscountDataInterface;
use Magento\Framework\Api\ExtensionAttributesInterface;

/**
 * Discount Data Model
 */
class DiscountData extends \Magento\Framework\Api\AbstractExtensibleObject implements DiscountDataInterface
{

    const AMOUNT = 'amount';
    const BASE_AMOUNT = 'base_amount';
    const ORIGINAL_AMOUNT = 'original_amount';
    const BASE_ORIGINAL_AMOUNT = 'base_original_amount';

    /**
     * Get Amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * Set Amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount(float $amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Get Base Amount
     *
     * @return float
     */
    public function getBaseAmount()
    {
        return $this->_get(self::BASE_AMOUNT);
    }

    /**
     * Set Base Amount
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseAmount(float $amount)
    {
        return $this->setData(self::BASE_AMOUNT, $amount);
    }

    /**
     * Get Original Amount
     *
     * @return float
     */
    public function getOriginalAmount()
    {
        return $this->_get(self::ORIGINAL_AMOUNT);
    }

    /**
     * Set Original Amount
     *
     * @param float $amount
     * @return $this
     */
    public function setOriginalAmount(float $amount)
    {
        return $this->setData(self::ORIGINAL_AMOUNT, $amount);
    }

    /**
     * Get Base Original Amount
     *
     * @return float
     */
    public function getBaseOriginalAmount()
    {
        return $this->_get(self::BASE_ORIGINAL_AMOUNT);
    }

    /**
     * Set Base Original Amount
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseOriginalAmount(float $amount)
    {
        return $this->setData(self::BASE_ORIGINAL_AMOUNT, $amount);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param ExtensionAttributesInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        ExtensionAttributesInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
