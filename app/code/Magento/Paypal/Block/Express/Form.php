<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Express;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Paypal\Helper\Data;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\Express\Checkout;

/**
 * Class \Magento\Paypal\Block\Express\Form
 *
 * @since 2.0.0
 */
class Form extends \Magento\Payment\Block\Form
{
    /**
     * Payment method code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_methodCode = Config::METHOD_WPP_EXPRESS;

    /**
     * Paypal data
     *
     * @var Data
     * @since 2.0.0
     */
    protected $_paypalData;

    /**
     * @var ConfigFactory
     * @since 2.0.0
     */
    protected $_paypalConfigFactory;

    /**
     * @var ResolverInterface
     * @since 2.0.0
     */
    protected $_localeResolver;

    /**
     * @var null
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_isScopePrivate;

    /**
     * @var CurrentCustomer
     * @since 2.0.0
     */
    protected $currentCustomer;

    /**
     * @param Context $context
     * @param ConfigFactory $paypalConfigFactory
     * @param ResolverInterface $localeResolver
     * @param Data $paypalData
     * @param CurrentCustomer $currentCustomer
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        ConfigFactory $paypalConfigFactory,
        ResolverInterface $localeResolver,
        Data $paypalData,
        CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        $this->_paypalData = $paypalData;
        $this->_paypalConfigFactory = $paypalConfigFactory;
        $this->_localeResolver = $localeResolver;
        $this->_config = null;
        $this->_isScopePrivate = true;
        $this->currentCustomer = $currentCustomer;
        parent::__construct($context, $data);
    }

    /**
     * Set template and redirect message
     *
     * @return null
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_config = $this->_paypalConfigFactory->create()
            ->setMethod($this->getMethodCode());
        $mark = $this->_getMarkTemplate();
        $mark->setPaymentAcceptanceMarkHref(
            $this->_config->getPaymentMarkWhatIsPaypalUrl($this->_localeResolver)
        )->setPaymentAcceptanceMarkSrc(
            $this->_config->getPaymentMarkImageUrl($this->_localeResolver->getLocale())
        );

        // known issue: code above will render only static mark image
        $this->_initializeRedirectTemplateWithMark($mark);
        parent::_construct();

        $this->setRedirectMessage(__('You will be redirected to the PayPal website.'));
    }

    /**
     * Payment method code getter
     *
     * @return string
     * @since 2.0.0
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }

    /**
     * Get initialized mark template
     *
     * @return Template
     * @since 2.0.0
     */
    protected function _getMarkTemplate()
    {
        /** @var $mark Template */
        $mark = $this->_layout->createBlock(\Magento\Framework\View\Element\Template::class);
        $mark->setTemplate('Magento_Paypal::payment/mark.phtml');
        return $mark;
    }

    /**
     * Initializes redirect template and set mark
     * @param Template $mark
     *
     * @return void
     * @since 2.0.0
     */
    protected function _initializeRedirectTemplateWithMark(Template $mark)
    {
        $this->setTemplate(
            'Magento_Paypal::payment/redirect.phtml'
        )->setRedirectMessage(
            __('You will be redirected to the PayPal website when you place an order.')
        )->setMethodTitle(
            // Output PayPal mark, omit title
            ''
        )->setMethodLabelAfterHtml(
            $mark->toHtml()
        );
    }

    /**
     * Get billing agreement code
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getBillingAgreementCode()
    {
        $customerId = $this->currentCustomer->getCustomerId();
        return $this->_paypalData->shouldAskToCreateBillingAgreement($this->_config, $customerId)
            ? Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT
            : null;
    }
}
