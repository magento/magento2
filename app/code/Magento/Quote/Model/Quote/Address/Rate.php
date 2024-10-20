<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

use Magento\Framework\Model\AbstractModel;

/**
 * @api
 * @method int getAddressId()
 * @method \Magento\Quote\Model\Quote\Address\Rate setAddressId(int $value)
 * @method string getCreatedAt()
 * @method \Magento\Quote\Model\Quote\Address\Rate setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Quote\Model\Quote\Address\Rate setUpdatedAt(string $value)
 * @method string getCarrier()
 * @method \Magento\Quote\Model\Quote\Address\Rate setCarrier(string $value)
 * @method string getCarrierTitle()
 * @method \Magento\Quote\Model\Quote\Address\Rate setCarrierTitle(string $value)
 * @method string getCode()
 * @method \Magento\Quote\Model\Quote\Address\Rate setCode(string $value)
 * @method string getMethod()
 * @method \Magento\Quote\Model\Quote\Address\Rate setMethod(string $value)
 * @method string getMethodDescription()
 * @method \Magento\Quote\Model\Quote\Address\Rate setMethodDescription(string $value)
 * @method float getPrice()
 * @method \Magento\Quote\Model\Quote\Address\Rate setPrice(float $value)
 * @method string getErrorMessage()
 * @method \Magento\Quote\Model\Quote\Address\Rate setErrorMessage(string $value)
 * @method string getMethodTitle()
 * @method \Magento\Quote\Model\Quote\Address\Rate setMethodTitle(string $value)
 *
 * @since 100.0.2
 */
class Rate extends AbstractModel
{
    /**
     * @var \Magento\Quote\Model\Quote\Address
     */
    protected $_address;

    /**
     * @var carrier_sort_order
     */
    public $carrier_sort_order;

    /**
     * Check the Quote rate
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Quote\Model\ResourceModel\Quote\Address\Rate::class);
    }

    /**
     * Set Address id with address before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        if ($this->getAddress()) {
            $this->setAddressId($this->getAddress()->getId());
        }
        return $this;
    }

    /**
     * Set address
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    public function setAddress(\Magento\Quote\Model\Quote\Address $address)
    {
        $this->_address = $address;
        return $this;
    }

    /**
     * Get Method for address
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Import shipping rate
     *
     * @param \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate
     * @return $this
     */
    public function importShippingRate(\Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate)
    {
        if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
            $this->setCode(
                $rate->getCarrier() . '_error'
            )->setCarrier(
                $rate->getCarrier()
            )->setCarrierTitle(
                $rate->getCarrierTitle()
            )->setErrorMessage(
                $rate->getErrorMessage()
            );
        } elseif ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            $this->setCode(
                $rate->getCarrier() . '_' . $rate->getMethod()
            )->setCarrier(
                $rate->getCarrier()
            )->setCarrierTitle(
                $rate->getCarrierTitle()
            )->setMethod(
                $rate->getMethod()
            )->setMethodTitle(
                $rate->getMethodTitle()
            )->setMethodDescription(
                $rate->getMethodDescription()
            )->setPrice(
                $rate->getPrice()
            );
        }
        return $this;
    }
}
