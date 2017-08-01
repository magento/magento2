<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Model;

/**
 * Class Checkmo
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 2.0.0
 */
class Checkmo extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_CHECKMO_CODE = 'checkmo';

    /**
     * Payment method code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = self::PAYMENT_METHOD_CHECKMO_CODE;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_formBlockType = \Magento\OfflinePayments\Block\Form\Checkmo::class;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_infoBlockType = \Magento\OfflinePayments\Block\Info\Checkmo::class;

    /**
     * Availability option
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isOffline = true;

    /**
     * @return string
     * @since 2.0.0
     */
    public function getPayableTo()
    {
        return $this->getConfigData('payable_to');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getMailingAddress()
    {
        return $this->getConfigData('mailing_address');
    }
}
