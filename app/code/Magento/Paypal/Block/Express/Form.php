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
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal Standard payment "form"
 */
namespace Magento\Paypal\Block\Express;

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
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Paypal\Helper\Data $paypalData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Paypal\Helper\Data $paypalData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = array()
    ) {
        $this->_paypalData = $paypalData;
        $this->_customerSession = $customerSession;
        parent::__construct($coreData, $context, $locale, $paypalConfigFactory, $data);
    }

    /**
     * Set template and redirect message
     */
    protected function _construct()
    {
        $result = parent::_construct();
        $this->setRedirectMessage(__('You will be redirected to the PayPal website.'));
        return $result;
    }

    /**
     * Set data to block
     *
     * @return \Magento\Core\Block\AbstractBlock
     */
    protected function _beforeToHtml()
    {
        $customerId = $this->_customerSession->getCustomerId();
        if ($this->_paypalData->shouldAskToCreateBillingAgreement($this->_config, $customerId)
            && $this->canCreateBillingAgreement()
        ) {
            $this->setCreateBACode(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
        }
        return parent::_beforeToHtml();
    }
}
