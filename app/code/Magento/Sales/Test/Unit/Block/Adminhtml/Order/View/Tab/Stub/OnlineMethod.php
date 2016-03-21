<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View\Tab\Stub;

/**
 * Stub for an online payment method
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class OnlineMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = false;
}
