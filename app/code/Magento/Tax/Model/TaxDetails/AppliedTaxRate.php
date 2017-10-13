<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\AppliedTaxRateInterface;

/**
 * @codeCoverageIgnore
 */
class AppliedTaxRate extends AbstractExtensibleModel implements AppliedTaxRateInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE    = 'code';
    const KEY_TITLE   = 'title';
    const KEY_PERCENT = 'percent';
    const KEY_AMOUNT  = 'amount';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(self::KEY_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPercent()
    {
        return $this->getData(self::KEY_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->getData(self::KEY_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        return $this->setData(self::KEY_TITLE, $title);
    }

    /**
     * {@inheritdoc}
     */
    public function setPercent($percent)
    {
        return $this->setData(self::KEY_PERCENT, $percent);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmount($amount)
    {
        return $this->setData(self::KEY_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
