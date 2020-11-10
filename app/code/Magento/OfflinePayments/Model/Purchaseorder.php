<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Purchaseorder
 *
 * Update additional payments fields and validate the payment data
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 100.0.2
 */
class Purchaseorder extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_PURCHASEORDER_CODE = 'purchaseorder';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_PURCHASEORDER_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = \Magento\OfflinePayments\Block\Form\Purchaseorder::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Magento\OfflinePayments\Block\Info\Purchaseorder::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        $this->getInfoInstance()->setPoNumber($data->getPoNumber());
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws LocalizedException
     * @api
     * @since 100.2.3
     */
    public function validate()
    {
        parent::validate();

        return $this;
    }
}
