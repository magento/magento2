<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Model;

/**
 * Cash on delivery payment method model
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 2.0.0
 */
class Cashondelivery extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_CASHONDELIVERY_CODE = 'cashondelivery';

    /**
     * Payment method code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = self::PAYMENT_METHOD_CASHONDELIVERY_CODE;

    /**
     * Cash On Delivery payment block paths
     *
     * @var string
     * @since 2.0.0
     */
    protected $_formBlockType = \Magento\OfflinePayments\Block\Form\Cashondelivery::class;

    /**
     * Info instructions block path
     *
     * @var string
     * @since 2.0.0
     */
    protected $_infoBlockType = \Magento\Payment\Block\Info\Instructions::class;

    /**
     * Availability option
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isOffline = true;

    /**
     * Get instructions text from config
     *
     * @return string
     * @since 2.0.0
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }
}
