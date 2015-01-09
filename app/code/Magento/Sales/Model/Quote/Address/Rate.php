<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote\Address;

use Magento\Framework\Model\AbstractModel;

/**
 * @method \Magento\Sales\Model\Resource\Quote\Address\Rate _getResource()
 * @method \Magento\Sales\Model\Resource\Quote\Address\Rate getResource()
 * @method int getAddressId()
 * @method \Magento\Sales\Model\Quote\Address\Rate setAddressId(int $value)
 * @method string getCreatedAt()
 * @method \Magento\Sales\Model\Quote\Address\Rate setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Sales\Model\Quote\Address\Rate setUpdatedAt(string $value)
 * @method string getCarrier()
 * @method \Magento\Sales\Model\Quote\Address\Rate setCarrier(string $value)
 * @method string getCarrierTitle()
 * @method \Magento\Sales\Model\Quote\Address\Rate setCarrierTitle(string $value)
 * @method string getCode()
 * @method \Magento\Sales\Model\Quote\Address\Rate setCode(string $value)
 * @method string getMethod()
 * @method \Magento\Sales\Model\Quote\Address\Rate setMethod(string $value)
 * @method string getMethodDescription()
 * @method \Magento\Sales\Model\Quote\Address\Rate setMethodDescription(string $value)
 * @method float getPrice()
 * @method \Magento\Sales\Model\Quote\Address\Rate setPrice(float $value)
 * @method string getErrorMessage()
 * @method \Magento\Sales\Model\Quote\Address\Rate setErrorMessage(string $value)
 * @method string getMethodTitle()
 * @method \Magento\Sales\Model\Quote\Address\Rate setMethodTitle(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Rate extends AbstractModel
{
    /**
     * @var \Magento\Sales\Model\Quote\Address
     */
    protected $_address;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Quote\Address\Rate');
    }

    /**
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
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function setAddress(\Magento\Sales\Model\Quote\Address $address)
    {
        $this->_address = $address;
        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Quote\Address
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Address\RateResult\AbstractResult $rate
     * @return $this
     */
    public function importShippingRate(\Magento\Sales\Model\Quote\Address\RateResult\AbstractResult $rate)
    {
        if ($rate instanceof \Magento\Sales\Model\Quote\Address\RateResult\Error) {
            $this->setCode(
                $rate->getCarrier() . '_error'
            )->setCarrier(
                $rate->getCarrier()
            )->setCarrierTitle(
                $rate->getCarrierTitle()
            )->setErrorMessage(
                $rate->getErrorMessage()
            );
        } elseif ($rate instanceof \Magento\Sales\Model\Quote\Address\RateResult\Method) {
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
