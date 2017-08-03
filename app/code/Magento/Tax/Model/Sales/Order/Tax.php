<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Order;

/**
 * @method \Magento\Tax\Model\ResourceModel\Sales\Order\Tax _getResource()
 * @method \Magento\Tax\Model\ResourceModel\Sales\Order\Tax getResource()
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
 * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Tax\Model\ResourceModel\Sales\Order\Tax::class);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTitle()
    {
        return $this->getData(self::KEY_TITLE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPercent()
    {
        return $this->getData(self::KEY_PERCENT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAmount()
    {
        return $this->getData(self::KEY_AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setBaseAmount($baseAmount)
    {
        return $this->setData(self::KEY_BASE_AMOUNT, $baseAmount);
    }

    /**
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxRateInterface[]
     * @since 2.1.0
     */
    public function getRates()
    {
        return $this->getData(self::KEY_RATES);
    }

    /**
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $rates
     * @return $this
     * @since 2.1.0
     */
    public function setRates($rates)
    {
        return $this->setData(self::KEY_RATES, $rates);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionInterface|null
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
