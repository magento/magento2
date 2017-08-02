<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\PayflowExpress;

/**
 * Wrapper that performs Paypal Express and Checkout communication
 * Use current Paypal Express method instance
 * @since 2.0.0
 */
class Checkout extends \Magento\Paypal\Model\Express\Checkout
{
    /**
     * Api Model Type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_apiType = \Magento\Paypal\Model\Api\PayflowNvp::class;

    /**
     * Payment method type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_methodType = \Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS;

    /**
     * Set shipping method to quote, if needed
     *
     * @param string $methodCode
     * @return void
     * @since 2.0.0
     */
    public function updateShippingMethod($methodCode)
    {
        parent::updateShippingMethod($methodCode);
        $this->quoteRepository->save($this->_quote);
    }
}
