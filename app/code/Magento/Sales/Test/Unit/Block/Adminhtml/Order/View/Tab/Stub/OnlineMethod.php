<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View\Tab\Stub;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Stub for an online payment method
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class OnlineMethod extends AbstractMethod
{
    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = false;
}
