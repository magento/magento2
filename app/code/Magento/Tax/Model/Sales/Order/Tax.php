<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Order;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\AppliedTaxRateInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax as ResourceSalesOrderTax;

/**
 * @method int getOrderId()
 * @method Tax setOrderId(int $value)
 * @method int getPriority()
 * @method Tax setPriority(int $value)
 * @method int getPosition()
 * @method Tax setPosition(int $value)
 * @method int getProcess()
 * @method Tax setProcess(int $value)
 * @method float getBaseRealAmount()
 * @method Tax setBaseRealAmount(float $value)
 * @codeCoverageIgnore
 */
class Tax extends AbstractExtensibleModel implements OrderTaxDetailsAppliedTaxInterface
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
        $this->_init(ResourceSalesOrderTax::class);
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
     * @return AppliedTaxRateInterface[]
     */
    public function getRates()
    {
        return $this->getData(self::KEY_RATES);
    }

    /**
     *
     * @param AppliedTaxRateInterface[] $rates
     * @return $this
     */
    public function setRates($rates)
    {
        return $this->setData(self::KEY_RATES, $rates);
    }

    /**
     * {@inheritdoc}
     *
     * @return OrderTaxDetailsAppliedTaxExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param OrderTaxDetailsAppliedTaxExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        OrderTaxDetailsAppliedTaxExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
