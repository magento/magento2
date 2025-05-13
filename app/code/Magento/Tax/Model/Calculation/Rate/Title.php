<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax Rate Title Model
 *
 * @method int getTaxCalculationRateId()
 */
namespace Magento\Tax\Model\Calculation\Rate;

use Magento\Tax\Api\Data\TaxRateTitleInterface;

class Title extends \Magento\Framework\Model\AbstractExtensibleModel implements TaxRateTitleInterface
{
    /**#@+
     *
     * Tax rate field key.
     */
    public const KEY_STORE_ID = 'store_id';
    public const KEY_VALUE_ID = 'value';
    /**#@-*/

    /**
     * Initialise the model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Tax\Model\ResourceModel\Calculation\Rate\Title::class);
    }

    /**
     * Delete a rate with specified ID
     *
     * @param int $rateId
     * @return $this
     */
    public function deleteByRateId($rateId)
    {
        $this->getResource()->deleteByRateId($rateId);
        return $this;
    }

    // @codeCoverageIgnoreStart

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return $this->getData(self::KEY_STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->getData(self::KEY_VALUE_ID);
    }

    /**
     * Set store id
     *
     * @param string $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::KEY_STORE_ID, $storeId);
    }

    /**
     * Set title value
     *
     * @param string $value
     * @return string
     */
    public function setValue($value)
    {
        return $this->setData(self::KEY_VALUE_ID, $value);
    }

    // @codeCoverageIgnoreEnd

    /**
     * @inheritDoc
     *
     * @return \Magento\Tax\Api\Data\TaxRateTitleExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     *
     * @param \Magento\Tax\Api\Data\TaxRateTitleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxRateTitleExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
