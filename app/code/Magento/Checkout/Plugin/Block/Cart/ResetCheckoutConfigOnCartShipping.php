<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Plugin\Block\Cart;

use Magento\Checkout\Block\Cart\Shipping;
use Magento\Checkout\Plugin\Block\AbstractResetCheckoutConfig;

/**
 * Class ResetCheckoutConfigOnCartShipping
 * Needed for reformat Customer Data address with custom attributes as options add labels for correct view on ShippingUI
 */
class ResetCheckoutConfigOnCartShipping extends AbstractResetCheckoutConfig
{
    /**
     * After Get Checkout Config
     *
     * @param Shipping $subject
     * @param mixed $result
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function afterGetSerializedCheckoutConfig(Shipping $subject, $result)
    {
        return $this->getSerializedCheckoutConfig($subject, $result);
    }
}
