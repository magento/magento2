<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Model;

/**
 * Class Purchaseorder
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 2.0.0
 */
class Purchaseorder extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_PURCHASEORDER_CODE = 'purchaseorder';

    /**
     * Payment method code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = self::PAYMENT_METHOD_PURCHASEORDER_CODE;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_formBlockType = \Magento\OfflinePayments\Block\Form\Purchaseorder::class;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_infoBlockType = \Magento\OfflinePayments\Block\Info\Purchaseorder::class;

    /**
     * Availability option
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isOffline = true;

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        $this->getInfoInstance()->setPoNumber($data->getPoNumber());
        return $this;
    }
}
