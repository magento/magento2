<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Block\PayflowExpress;

class Form extends \Magento\Paypal\Block\Express\Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = \Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS;

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
