<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax Rate Title Model
 *
 * @method \Magento\Tax\Model\Resource\Calculation\Rate\Title _getResource()
 * @method \Magento\Tax\Model\Resource\Calculation\Rate\Title getResource()
 * @method int getTaxCalculationRateId()
 * @method \Magento\Tax\Model\Calculation\Rate\Title setTaxCalculationRateId(int $value)
 * @method int getStoreId()
 * @method \Magento\Tax\Model\Calculation\Rate\Title setStoreId(int $value)
 * @method string getValue()
 * @method \Magento\Tax\Model\Calculation\Rate\Title setValue(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model\Calculation\Rate;

class Title extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Tax\Api\Data\TaxRateTitleInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Tax\Model\Resource\Calculation\Rate\Title');
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
    // @codeCoverageIgnoreEnd
}
