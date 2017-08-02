<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Payflow\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Paypal\Model\Config;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;

/**
 * @api
 * @since 2.1.0
 */
class CardRenderer extends AbstractCardRenderer
{
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     * @since 2.1.0
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === Config::METHOD_PAYFLOWPRO;
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getNumberLast4Digits()
    {
        return $this->getTokenDetails()['cc_last_4'];
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getExpDate()
    {
        return $this->getTokenDetails()['cc_exp_month'] . '/' . $this->getTokenDetails()['cc_exp_year'];
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getIconUrl()
    {
        return $this->getIconForType($this->getTokenDetails()['cc_type'])['url'];
    }

    /**
     * @return int
     * @since 2.1.0
     */
    public function getIconHeight()
    {
        return $this->getIconForType($this->getTokenDetails()['cc_type'])['height'];
    }

    /**
     * @return int
     * @since 2.1.0
     */
    public function getIconWidth()
    {
        return $this->getIconForType($this->getTokenDetails()['cc_type'])['width'];
    }
}
