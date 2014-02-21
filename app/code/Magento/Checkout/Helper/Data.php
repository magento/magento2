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
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Checkout default helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Checkout\Helper;

class Data extends \Magento\App\Helper\AbstractHelper
{
    const XML_PATH_GUEST_CHECKOUT = 'checkout/options/guest_checkout';
    const XML_PATH_CUSTOMER_MUST_BE_LOGGED = 'checkout/options/customer_must_be_logged';

    protected $_agreements = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @var \Magento\Checkout\Model\Resource\Agreement\CollectionFactory
     */
    protected $_agreementCollectionFactory;

    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $_emailTemplFactory;

    /**
     * Translator model
     *
     * @var \Magento\TranslateInterface
     */
    protected $_translator;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Checkout\Model\Resource\Agreement\CollectionFactory $agreementCollectionFactory
     * @param \Magento\Email\Model\TemplateFactory $emailTemplFactory
     * @param \Magento\TranslateInterface $translator
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Checkout\Model\Resource\Agreement\CollectionFactory $agreementCollectionFactory,
        \Magento\Email\Model\TemplateFactory $emailTemplFactory,
        \Magento\TranslateInterface $translator
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_locale = $locale;
        $this->_agreementCollectionFactory = $agreementCollectionFactory;
        $this->_emailTemplFactory = $emailTemplFactory;
        $this->_translator = $translator;
        parent::__construct($context);
    }

    /**
     * Retrieve checkout session model
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Retrieve checkout quote model object
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    public function formatPrice($price)
    {
        return $this->getQuote()->getStore()->formatPrice($price);
    }

    public function convertPrice($price, $format=true)
    {
        return $this->getQuote()->getStore()->convertPrice($price, $format);
    }

    public function getRequiredAgreementIds()
    {
        if (is_null($this->_agreements)) {
            if (!$this->_coreStoreConfig->getConfigFlag('checkout/options/enable_agreements')) {
                $this->_agreements = array();
            } else {
                $this->_agreements = $this->_agreementCollectionFactory->create()
                    ->addStoreFilter($this->_storeManager->getStore()->getId())
                    ->addFieldToFilter('is_active', 1)
                    ->getAllIds();
            }
        }
        return $this->_agreements;
    }

    /**
     * Get onepage checkout availability
     *
     * @return bool
     */
    public function canOnepageCheckout()
    {
        return (bool)$this->_coreStoreConfig->getConfig('checkout/options/onepage_checkout_enabled');
    }

    /**
     * Get sales item (quote item, order item etc) price including tax based on row total and tax amount
     *
     * @param   \Magento\Object $item
     * @return  float
     */
    public function getPriceInclTax($item)
    {
        if ($item->getPriceInclTax()) {
            return $item->getPriceInclTax();
        }
        $qty = ($item->getQty() ? $item->getQty() : ($item->getQtyOrdered() ? $item->getQtyOrdered() : 1));
        $taxAmount = $item->getTaxAmount() + $item->getDiscountTaxCompensation();
        $price = (floatval($qty)) ? ($item->getRowTotal() + $taxAmount)/$qty : 0;
        return $this->_storeManager->getStore()->roundPrice($price);
    }

    /**
     * Get sales item (quote item, order item etc) row total price including tax
     *
     * @param   \Magento\Object $item
     * @return  float
     */
    public function getSubtotalInclTax($item)
    {
        if ($item->getRowTotalInclTax()) {
            return $item->getRowTotalInclTax();
        }
        $tax = $item->getTaxAmount() + $item->getDiscountTaxCompensation();
        return $item->getRowTotal() + $tax;
    }

    public function getBasePriceInclTax($item)
    {
        $qty = ($item->getQty() ? $item->getQty() : ($item->getQtyOrdered() ? $item->getQtyOrdered() : 1));
        $taxAmount = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();
        $price = (floatval($qty)) ? ($item->getBaseRowTotal() + $taxAmount)/$qty : 0;
        return $this->_storeManager->getStore()->roundPrice($price);
    }

    public function getBaseSubtotalInclTax($item)
    {
        $tax = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();
        return $item->getBaseRowTotal()+$tax;
    }

    /**
     * Send email id payment was failed
     *
     * @param \Magento\Sales\Model\Quote $checkout
     * @param string $message
     * @param string $checkoutType
     * @return \Magento\Checkout\Helper\Data
     */
    public function sendPaymentFailedEmail($checkout, $message, $checkoutType = 'onepage')
    {
        $this->_translator->setTranslateInline(false);

        /** @var \Magento\Email\Model\Template $mailTemplate */
        $mailTemplate = $this->_emailTemplFactory->create();

        $template = $this->_coreStoreConfig->getConfig('checkout/payment_failed/template', $checkout->getStoreId());

        $copyTo = $this->_getEmails('checkout/payment_failed/copy_to', $checkout->getStoreId());
        $copyMethod = $this->_coreStoreConfig->getConfig(
            'checkout/payment_failed/copy_method', $checkout->getStoreId()
        );
        if ($copyTo && $copyMethod == 'bcc') {
            $mailTemplate->addBcc($copyTo);
        }

        $_receiver = $this->_coreStoreConfig->getConfig('checkout/payment_failed/receiver', $checkout->getStoreId());
        $sendTo = array(
            array(
                'email' => $this->_coreStoreConfig->getConfig(
                        'trans_email/ident_' . $_receiver . '/email', $checkout->getStoreId()
                    ),
                'name'  => $this->_coreStoreConfig->getConfig(
                        'trans_email/ident_' . $_receiver . '/name', $checkout->getStoreId()
                    )
            )
        );

        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'email' => $email,
                    'name'  => null
                );
            }
        }
        $shippingMethod = '';
        if ($shippingInfo = $checkout->getShippingAddress()->getShippingMethod()) {
            $data = explode('_', $shippingInfo);
            $shippingMethod = $data[0];
        }

        $paymentMethod = '';
        if ($paymentInfo = $checkout->getPayment()) {
            $paymentMethod = $paymentInfo->getMethod();
        }

        $items = '';
        foreach ($checkout->getAllVisibleItems() as $_item) {
            /* @var $_item \Magento\Sales\Model\Quote\Item */
            $items .= $_item->getProduct()->getName() . '  x '. $_item->getQty() . '  '
                    . $checkout->getStoreCurrencyCode() . ' '
                    . $_item->getProduct()->getFinalPrice($_item->getQty()) . "\n";
        }
        $total = $checkout->getStoreCurrencyCode() . ' ' . $checkout->getGrandTotal();

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array(
                'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND,
                'store' => $checkout->getStoreId()
            ))
                ->sendTransactional(
                    $template,
                    $this->_coreStoreConfig->getConfig('checkout/payment_failed/identity', $checkout->getStoreId()),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'reason' => $message,
                        'checkoutType' => $checkoutType,
                        'dateAndTime' => $this->_locale->date(),
                        'customer' => $checkout->getCustomerFirstname() . ' ' . $checkout->getCustomerLastname(),
                        'customerEmail' => $checkout->getCustomerEmail(),
                        'billingAddress' => $checkout->getBillingAddress(),
                        'shippingAddress' => $checkout->getShippingAddress(),
                        'shippingMethod' => $this->_coreStoreConfig->getConfig('carriers/'.$shippingMethod.'/title'),
                        'paymentMethod' => $this->_coreStoreConfig->getConfig('payment/'.$paymentMethod.'/title'),
                        'items' => nl2br($items),
                        'total' => $total
                    )
                );
        }

        $this->_translator->setTranslateInline(true);

        return $this;
    }

    protected function _getEmails($configPath, $storeId)
    {
        $data = $this->_coreStoreConfig->getConfig($configPath, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    /**
     * Check is allowed Guest Checkout
     * Use config settings and observer
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @param int|\Magento\Core\Model\Store $store
     * @return bool
     */
    public function isAllowedGuestCheckout(\Magento\Sales\Model\Quote $quote, $store = null)
    {
        if ($store === null) {
            $store = $quote->getStoreId();
        }
        $guestCheckout = $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_GUEST_CHECKOUT, $store);

        if ($guestCheckout == true) {
            $result = new \Magento\Object();
            $result->setIsAllowed($guestCheckout);
            $this->_eventManager->dispatch('checkout_allow_guest', array(
                'quote'  => $quote,
                'store'  => $store,
                'result' => $result
            ));

            $guestCheckout = $result->getIsAllowed();
        }

        return $guestCheckout;
    }

    /**
     * Check if context is checkout
     *
     * @return bool
     */
    public function isContextCheckout()
    {
        return ($this->_request->getParam('context') == 'checkout');
    }

    /**
     * Check if user must be logged during checkout process
     *
     * @return boolean
     */
    public function isCustomerMustBeLogged()
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_CUSTOMER_MUST_BE_LOGGED);
    }
}
