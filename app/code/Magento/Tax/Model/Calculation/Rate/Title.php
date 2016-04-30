<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax Rate Title Model
 *
 * @method \Magento\Tax\Model\ResourceModel\Calculation\Rate\Title _getResource()
 * @method \Magento\Tax\Model\ResourceModel\Calculation\Rate\Title getResource()
 * @method int getTaxCalculationRateId()
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model\Calculation\Rate;

use Magento\Tax\Api\Data\TaxRateTitleInterface;

class Title extends \Magento\Framework\Model\AbstractExtensibleModel implements TaxRateTitleInterface
{
    /**#@+
     *
     * Tax rate field key.
     */
    const KEY_STORE_ID = 'store_id';
    const KEY_VALUE_ID = 'value';
    /**#@-*/

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Tax\Model\ResourceModel\Calculation\Rate\Title');
    }

    /**
     * @param int $rateId
     * @return $this
     */
    public function deleteByRateId($rateId)
    {
        $this->getResource()->deleteByRateId($rateId);
        return $this;
    }

    /**
     * @codeCoverageIgnoreStart
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::KEY_STORE_ID);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\TaxRateTitleExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\TaxRateTitleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxRateTitleExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
