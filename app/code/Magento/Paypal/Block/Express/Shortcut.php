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

use Magento\Catalog\Block as CatalogBlock;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;

/**
 * Paypal express checkout shortcut link
 */
class Shortcut extends \Magento\Framework\View\Element\Template implements CatalogBlock\ShortcutInterface
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
    protected $_paymentMethodCode = '';

    /**
     * Start express action
     *
     * @var string
     */
    protected $_startAction = '';

    /**
     * Express checkout model factory name
     *
     * @var string
     */
    protected $_checkoutType = '';

    /**
     * Shortcut alias
     *
     * @var string
     */
    protected $_alias = '';

    /**
     * Paypal data
     *
     * @var \Magento\Paypal\Helper\Data
     */
    protected $_paypalData;

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
     * @var \Magento\Framework\Math\Random
     */
    protected $_mathRandom;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var ValidatorInterface
     */
    private $_shortcutValidator;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Paypal\Helper\Data $paypalData
     * @param \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory
     * @param \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param ValidatorInterface $shortcutValidator
     * @param string $paymentMethodCode
     * @param string $startAction
     * @param string $checkoutType
     * @param string $alias
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Paypal\Helper\Data $paypalData,
        \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory,
        \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        ValidatorInterface $shortcutValidator,
        $paymentMethodCode,
        $startAction,
        $checkoutType,
        $alias,
        \Magento\Checkout\Model\Session $checkoutSession = null,
        array $data = array()
    ) {
        $this->_paypalData = $paypalData;
        $this->_paypalConfigFactory = $paypalConfigFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_checkoutFactory = $checkoutFactory;
        $this->_mathRandom = $mathRandom;
        $this->_localeResolver = $localeResolver;
        $this->_shortcutValidator = $shortcutValidator;

        $this->_paymentMethodCode = $paymentMethodCode;
        $this->_startAction = $startAction;
        $this->_checkoutType = $checkoutType;
        $this->_alias = $alias;

        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();
        /** @var \Magento\Paypal\Model\Config $config */
        $config = $this->_paypalConfigFactory->create();
        $config->setMethod($this->_paymentMethodCode);

        $isInCatalog = $this->getIsInCatalogProduct();

        if (!$this->_shortcutValidator->validate($this->_paymentMethodCode, $isInCatalog)) {
            $this->_shouldRender = false;
            return $result;
        }

        $quote = $isInCatalog || !$this->_checkoutSession ? null : $this->_checkoutSession->getQuote();

        // set misc data
        $this->setShortcutHtmlId(
            $this->_mathRandom->getUniqueHash('ec_shortcut_')
        )->setCheckoutUrl(
            $this->getUrl($this->_startAction)
        );

        // use static image if in catalog
        if ($isInCatalog || null === $quote) {
            $this->setImageUrl($config->getExpressCheckoutShortcutImageUrl($this->_localeResolver->getLocaleCode()));
        } else {
            /**@todo refactor checkout model. Move getCheckoutShortcutImageUrl to helper or separate model */
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
        return $this->_alias;
    }
}
