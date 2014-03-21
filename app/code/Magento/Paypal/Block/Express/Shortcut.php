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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Block\Express;

use Magento\Catalog\Block as CatalogBlock;

/**
 * Paypal express checkout shortcut link
 */
class Shortcut extends \Magento\View\Element\Template implements CatalogBlock\ShortcutInterface
{
    /**
     * Whether the block should be eventually rendered
     *
     * @var bool
     */
    protected $_shouldRender = true;

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_paymentMethodCode = \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS;

    /**
     * Start express action
     *
     * @var string
     */
    protected $_startAction = 'paypal/express/start';

    /**
     * Express checkout model factory name
     *
     * @var string
     */
    protected $_checkoutType = 'Magento\Paypal\Model\Express\Checkout';

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_registry;

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

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
     * @var \Magento\Paypal\Model\ConfigFactory
     */
    protected $_paypalConfigFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Paypal\Model\Express\Checkout\Factory
     */
    protected $_checkoutFactory;

    /**
     * @var \Magento\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var \Magento\Customer\Service\V1\CustomerCurrentService
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Paypal\Helper\Data $paypalData
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory
     * @param \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Customer\Service\V1\CustomerCurrentService $currentCustomer
     * @param \Magento\Locale\ResolverInterface $localeResolver
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Paypal\Helper\Data $paypalData,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory,
        \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
        \Magento\Math\Random $mathRandom,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Customer\Service\V1\CustomerCurrentService $currentCustomer,
        \Magento\Locale\ResolverInterface $localeResolver,
        \Magento\Checkout\Model\Session $checkoutSession = null,
        array $data = array()
    ) {
        $this->_registry = $registry;
        $this->_paypalData = $paypalData;
        $this->_paymentData = $paymentData;
        $this->_customerSession = $customerSession;
        $this->_paypalConfigFactory = $paypalConfigFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_checkoutFactory = $checkoutFactory;
        $this->mathRandom = $mathRandom;
        $this->productTypeConfig = $productTypeConfig;
        $this->_localeResolver = $localeResolver;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * @return \Magento\View\Element\AbstractBlock
     */
    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();
        $params = array($this->_paymentMethodCode);
        $config = $this->_paypalConfigFactory->create(array('params' => $params));
        $isInCatalog = $this->getIsInCatalogProduct();
        $quote = $isInCatalog || !$this->_checkoutSession ? null : $this->_checkoutSession->getQuote();

        // check visibility on cart or product page
        $context = $isInCatalog ? 'visible_on_product' : 'visible_on_cart';
        if (!$config->{$context}) {
            $this->_shouldRender = false;
            return $result;
        }

        if ($isInCatalog) {
            // Show PayPal shortcut on a product view page only if product has nonzero price
            /** @var $currentProduct \Magento\Catalog\Model\Product */
            $currentProduct = $this->_registry->registry('current_product');
            if (!is_null($currentProduct)) {
                $productPrice = (double)$currentProduct->getFinalPrice();
                if (empty($productPrice) && !$this->productTypeConfig->isProductSet($currentProduct->getTypeId())) {
                    $this->_shouldRender = false;
                    return $result;
                }
            }
        }
        // validate minimum quote amount and validate quote for zero grandtotal
        if (null !== $quote && (!$quote->validateMinimumAmount() ||
            !$quote->getGrandTotal() && !$quote->hasNominalItems())
        ) {
            $this->_shouldRender = false;
            return $result;
        }

        // check payment method availability
        $methodInstance = $this->_paymentData->getMethodInstance($this->_paymentMethodCode);
        if (!$methodInstance || !$methodInstance->isAvailable($quote)) {
            $this->_shouldRender = false;
            return $result;
        }

        // set misc data
        $this->setShortcutHtmlId(
            $this->mathRandom->getUniqueHash('ec_shortcut_')
        )->setCheckoutUrl(
            $this->getUrl($this->_startAction)
        );

        // use static image if in catalog
        if ($isInCatalog || null === $quote) {
            $this->setImageUrl($config->getExpressCheckoutShortcutImageUrl($this->_localeResolver->getLocaleCode()));
        } else {
            $parameters = array('params' => array('quote' => $quote, 'config' => $config));
            $checkoutModel = $this->_checkoutFactory->create($this->_checkoutType, $parameters);
            $this->setImageUrl($checkoutModel->getCheckoutShortcutImageUrl());
        }

        // ask whether to create a billing agreement
        $customerId = $this->currentCustomer->getCustomerId(); // potential issue for caching
        if ($this->_paypalData->shouldAskToCreateBillingAgreement($config, $customerId)) {
            $this->setConfirmationUrl(
                $this->getUrl(
                    $this->_startAction,
                    array(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT => 1)
                )
            );
            $this->setConfirmationMessage(
                __('Would you like to sign a billing agreement ' . 'to streamline further purchases with PayPal?')
            );
        }

        return $result;
    }

    /**
     * Render the block if needed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_shouldRender) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Check is "OR" label position before shortcut
     *
     * @return bool
     */
    public function isOrPositionBefore()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_BEFORE;
    }

    /**
     * Check is "OR" label position after shortcut
     *
     * @return bool
     */
    public function isOrPositionAfter()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_AFTER;
    }

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return 'product.info.addtocart.paypal';
    }
}
