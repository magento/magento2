<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Express;

use Magento\Paypal\Model\Config;
use Magento\Catalog\Block as CatalogBlock;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;

/**
 * Paypal express checkout shortcut link
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Shortcut extends \Magento\Framework\View\Element\Template implements CatalogBlock\ShortcutInterface
{
    /**
     * Whether the block should be eventually rendered
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_shouldRender = true;

    /**
     * Payment method code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_paymentMethodCode = '';

    /**
     * Start express action
     *
     * @var string
     * @since 2.0.0
     */
    protected $_startAction = '';

    /**
     * Express checkout model factory name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_checkoutType = '';

    /**
     * Shortcut alias
     *
     * @var string
     * @since 2.0.0
     */
    protected $_alias = '';

    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     * @since 2.0.0
     */
    protected $_paypalConfigFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Paypal\Model\Express\Checkout\Factory
     * @since 2.0.0
     */
    protected $_checkoutFactory;

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    protected $_mathRandom;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $_localeResolver;

    /**
     * @var ValidatorInterface
     * @since 2.0.0
     */
    private $_shortcutValidator;

    /**
     * @var Config
     * @since 2.1.0
     */
    private $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory
     * @param \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param ValidatorInterface $shortcutValidator
     * @param string $paymentMethodCode
     * @param string $startAction
     * @param string $checkoutType
     * @param string $alias
     * @param string $shortcutTemplate
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory,
        \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        ValidatorInterface $shortcutValidator,
        $paymentMethodCode,
        $startAction,
        $checkoutType,
        $alias,
        $shortcutTemplate,
        \Magento\Checkout\Model\Session $checkoutSession = null,
        array $data = []
    ) {
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
        $this->setTemplate($shortcutTemplate);

        parent::__construct($context, $data);

        $this->config = $this->_paypalConfigFactory->create();
        $this->config->setMethod($this->_paymentMethodCode);
    }

    /**
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();

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
            $this->setImageUrl($this->config->getExpressCheckoutShortcutImageUrl($this->_localeResolver->getLocale()));
        } else {
            /**@todo refactor checkout model. Move getCheckoutShortcutImageUrl to helper or separate model */
            $parameters = ['params' => ['quote' => $quote, 'config' => $this->config]];
            $checkoutModel = $this->_checkoutFactory->create($this->_checkoutType, $parameters);
            $this->setImageUrl($checkoutModel->getCheckoutShortcutImageUrl());
        }

        return $result;
    }

    /**
     * Render the block if needed
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @return bool
     * @since 2.1.0
     */
    protected function shouldRender()
    {
        $this->config->setMethod(Config::METHOD_EXPRESS);

        $isInCatalog = $this->getIsInCatalogProduct();
        $isInContext = (bool)(int) $this->config->getValue('in_context');

        $isEnabled = ($isInContext && $isInCatalog) || !$isInContext;
        $this->config->setMethod($this->_paymentMethodCode);

        return $isEnabled && $this->_shouldRender;
    }

    /**
     * Check is "OR" label position before shortcut
     *
     * @return bool
     * @since 2.0.0
     */
    public function isOrPositionBefore()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_BEFORE;
    }

    /**
     * Check is "OR" label position after shortcut
     *
     * @return bool
     * @since 2.0.0
     */
    public function isOrPositionAfter()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_AFTER;
    }

    /**
     * Get shortcut alias
     *
     * @return string
     * @since 2.0.0
     */
    public function getAlias()
    {
        return $this->_alias;
    }
}
