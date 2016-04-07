<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Paypal;

use Magento\Checkout\Model\Session;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Payment\Model\MethodInterface;

/**
 * Class Button
 */
class Button extends Template implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    const BUTTON_ELEMENT_INDEX = 'button_id';

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var MethodInterface
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
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
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
    public function getLocale()
    {
        return strtolower($this->localeResolver->getLocale());
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->checkoutSession->getQuote()->getCurrency()->getBaseCurrencyCode();
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->checkoutSession->getQuote()->getBaseGrandTotal();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->payment->isAvailable($this->checkoutSession->getQuote()) &&
            $this->config->isDisplayShoppingCart();
    }

    /**
     * @return string
     */
    public function getMerchantName()
    {
        return $this->config->getMerchantName();
    }

    /**
     * @return string|null
     */
    public function getClientToken()
    {
        return $this->configProvider->getClientToken();
    }

    /**
     * @return string
     */
    public function getActionSuccess()
    {
        return $this->getUrl(ConfigProvider::CODE . '/paypal/review', ['_secure' => true]);
    }
}
