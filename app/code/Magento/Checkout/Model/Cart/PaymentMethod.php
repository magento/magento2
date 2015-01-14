<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Cart;

class PaymentMethod extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Checkout\Api\Data\PaymentMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData('title');
    }
}
