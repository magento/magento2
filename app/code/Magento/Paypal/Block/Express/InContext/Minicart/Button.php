<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Express\InContext\Minicart;

use Magento\Checkout\Model\Session;
use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Block\Express\InContext;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Button
 */
class Button extends Template implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    const PAYPAL_BUTTON_ID = 'paypal-express-in-context-checkout-main';

    const BUTTON_ELEMENT_INDEX = 'button_id';

    const CART_BUTTON_ELEMENT_INDEX = 'add_to_cart_selector';

    /**
     * @var bool
     */
    private $isMiniCart = false;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var MethodInterface
     */
    private $payment;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param ConfigFactory $configFactory
     * @param MethodInterface $payment
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        ConfigFactory $configFactory,
        Session $session,
        MethodInterface $payment,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();
        $this->config->setMethod(Config::METHOD_EXPRESS);
        $this->payment = $payment;
        $this->session = $session;
    }

    /**
     * Check `in_context` config value
     *
     * @return bool
     */
    private function isInContext()
    {
        return (bool)(int) $this->config->getValue('in_context');
    }

    /**
     * Check `visible_on_cart` config value
     *
     * @return bool
     */
    private function isVisibleOnCart()
    {
        return (bool)(int) $this->config->getValue('visible_on_cart');
    }

    /**
     * Check is Paypal In-Context Express Checkout button
     * should render in cart/mini-cart
     *
     * @return bool
     */
    protected function shouldRender()
    {
        return $this->payment->isAvailable($this->session->getQuote())
            && $this->isMiniCart
            && $this->isInContext()
            && $this->isVisibleOnCart();
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getContainerId()
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getAddToCartSelector()
    {
        return $this->getData(self::CART_BUTTON_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->config->getExpressCheckoutInContextImageUrl(
            $this->localeResolver->getLocale()
        );
    }

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @param bool $isCatalog
     * @return $this
     */
    public function setIsInCatalogProduct($isCatalog)
    {
        $this->isMiniCart = !$isCatalog;

        return $this;
    }
}
