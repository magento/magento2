<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Checkout\Api\Data\TotalsInformationInterface;

/**
 * @codeCoverageIgnoreStart
 * @since 2.0.0
 */
class TotalsInformation extends AbstractExtensibleModel implements TotalsInformationInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setAddress(\Magento\Quote\Api\Data\AddressInterface $address)
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getShippingMethodCode()
    {
        return $this->getData(self::SHIPPING_METHOD_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setShippingMethodCode($code)
    {
        return $this->setData(self::SHIPPING_METHOD_CODE, $code);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getShippingCarrierCode()
    {
        return $this->getData(self::SHIPPING_CARRIER_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setShippingCarrierCode($code)
    {
        return $this->setData(self::SHIPPING_CARRIER_CODE, $code);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Checkout\Api\Data\TotalsInformationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
