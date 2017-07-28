<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Hosted\Pro;

/**
 * Hosted Pro iframe block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Iframe extends \Magento\Paypal\Block\Iframe
{
    /**
     * Internal constructor
     * Set payment method code
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_paymentMethodCode = \Magento\Paypal\Model\Config::METHOD_HOSTEDPRO;
    }

    /**
     * Get iframe action URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getFrameActionUrl()
    {
        return $this->_getOrder()->getPayment()->getAdditionalInformation('secure_form_url');
    }
}
