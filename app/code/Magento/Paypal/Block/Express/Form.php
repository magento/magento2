<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Block\Express;

/**
 * PayPal Standard payment "form"
 */
class Form extends \Magento\Paypal\Block\Standard\Form
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_methodCode = \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS;

    /**
     * Paypal data
     *
     * @var \Magento\Paypal\Helper\Data
     */
    protected $_paypalData;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Paypal\Helper\Data $paypalData
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Paypal\Helper\Data $paypalData,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = array()
    ) {
        $this->_paypalData = $paypalData;
        $this->currentCustomer = $currentCustomer;
        parent::__construct($context, $paypalConfigFactory, $localeResolver, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Set template and redirect message
     *
     * @return null
     */
    protected function _construct()
    {
        $result = parent::_construct();
        $this->setRedirectMessage(__('You will be redirected to the PayPal website.'));
        return $result;
    }

    /**
     * Get billing agreement code
     *
     * @return string|null
     */
    public function getBillingAgreementCode()
    {
        $customerId = $this->currentCustomer->getCustomerId();
        return $this->_paypalData->shouldAskToCreateBillingAgreement($this->_config, $customerId)
            ? \Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT : null;
    }
}
