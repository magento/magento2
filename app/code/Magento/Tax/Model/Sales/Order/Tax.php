<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Order;

/**
 * @method int getOrderId()
 * @method \Magento\Tax\Model\Sales\Order\Tax setOrderId(int $value)
 * @method int getPriority()
 * @method \Magento\Tax\Model\Sales\Order\Tax setPriority(int $value)
 * @method int getPosition()
 * @method \Magento\Tax\Model\Sales\Order\Tax setPosition(int $value)
 * @method int getProcess()
 * @method \Magento\Tax\Model\Sales\Order\Tax setProcess(int $value)
 * @method float getBaseRealAmount()
 * @method \Magento\Tax\Model\Sales\Order\Tax setBaseRealAmount(float $value)
 * @codeCoverageIgnore
 */
class Tax extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE        = 'code';
    const KEY_TITLE       = 'title';
    const KEY_PERCENT     = 'percent';
    const KEY_AMOUNT      = 'amount';
    const KEY_BASE_AMOUNT = 'base_amount';
    const KEY_RATES       = 'rates';
    /**#@-*/

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Tax\Model\ResourceModel\Sales\Order\Tax::class);
    }

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
    public function getBaseAmount()
    {
        return $this->getData(self::KEY_BASE_AMOUNT);
    }

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Set Title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::KEY_TITLE, $title);
    }

    /**
     * Set Tax Percent
     *
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent)
    {
        return $this->setData(self::KEY_PERCENT, $percent);
    }

    /**
     * Set tax amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        return $this->setData(self::KEY_AMOUNT, $amount);
    }

    /**
     * Set tax amount in base currency
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount)
    {
        return $this->setData(self::KEY_BASE_AMOUNT, $baseAmount);
    }

    /**
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxRateInterface[]
     */
    public function getRates()
    {
        return $this->getData(self::KEY_RATES);
    }

    /**
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $rates
     * @return $this
     */
    public function setRates($rates)
    {
        return $this->setData(self::KEY_RATES, $rates);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
