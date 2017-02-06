<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\PayflowExpress;

use Magento\Paypal\Model\Config;

class Form extends \Magento\Paypal\Block\Express\Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = Config::METHOD_WPP_PE_EXPRESS;

    /**
     * No billing agreements available for payflow express
     *
     * @return string|null
     */
    public function getBillingAgreementCode()
    {
        return false;
    }
}
