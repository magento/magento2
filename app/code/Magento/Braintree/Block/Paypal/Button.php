<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Paypal;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;

/**
 * Class Button
 * @since 2.1.0
 */
class Button extends Template implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    const BUTTON_ELEMENT_INDEX = 'button_id';

    /**
     * @var ResolverInterface
     * @since 2.1.0
     */
    private $localeResolver;

    /**
     * @var Session
     * @since 2.1.0
     */
    private $checkoutSession;

    /**
     * @var Config
     * @since 2.1.0
     */
    private $config;

    /**
     * @var ConfigProvider
     * @since 2.1.0
     */
    private $configProvider;

    /**
     * @var MethodInterface
     * @since 2.1.0
     */
    private $payment;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param Session $checkoutSession
     * @param Config $config
     * @param ConfigProvider $configProvider
     * @param MethodInterface $payment
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        Session $checkoutSession,
        Config $config,
        ConfigProvider $configProvider,
        MethodInterface $payment,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->localeResolver = $localeResolver;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->configProvider = $configProvider;
        $this->payment = $payment;
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    protected function _toHtml()
    {
        if ($this->isActive()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getContainerId()
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getLocale()
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getCurrency()
    {
        return $this->checkoutSession->getQuote()->getCurrency()->getBaseCurrencyCode();
    }

    /**
     * @return float
     * @since 2.1.0
     */
    public function getAmount()
    {
        return $this->checkoutSession->getQuote()->getBaseGrandTotal();
    }

    /**
     * @return bool
     * @since 2.1.0
     */
    public function isActive()
    {
        return $this->payment->isAvailable($this->checkoutSession->getQuote()) &&
            $this->config->isDisplayShoppingCart();
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getMerchantName()
    {
        return $this->config->getMerchantName();
    }

    /**
     * @return string|null
     * @since 2.1.0
     */
    public function getClientToken()
    {
        return $this->configProvider->getClientToken();
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getActionSuccess()
    {
        return $this->getUrl(ConfigProvider::CODE . '/paypal/review', ['_secure' => true]);
    }
}
