<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Block\Express\InContext\Minicart;

use Magento\Checkout\Model\Session;
use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Paypal\Model\SmartButtonConfig;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Button
 */
class SmartButton extends Template implements ShortcutInterface
{
    private const ALIAS_ELEMENT_INDEX = 'alias';

    const PAYPAL_BUTTON_ID = 'paypal-express-in-context-checkout-main';

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SmartButtonConfig
     */
    private $smartButtonConfig;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var QuoteIdToMaskedQuoteId
     */
    private $quoteIdMask;

    /**
     * @param Context $context
     * @param ConfigFactory $configFactory
     * @param Session $session
     * @param MethodInterface $payment
     * @param SerializerInterface $serializer
     * @param SmartButtonConfig $smartButtonConfig
     * @param UrlInterface $urlBuilder
     * @param QuoteIdToMaskedQuoteId $quoteIdToMaskedQuoteId
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigFactory $configFactory,
        Session $session,
        MethodInterface $payment,
        SerializerInterface $serializer,
        SmartButtonConfig $smartButtonConfig,
        UrlInterface $urlBuilder,
        QuoteIdToMaskedQuoteId $quoteIdToMaskedQuoteId,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $configFactory->create();
        $this->config->setMethod(Config::METHOD_EXPRESS);
        $this->payment = $payment;
        $this->session = $session;
        $this->serializer = $serializer;
        $this->smartButtonConfig = $smartButtonConfig;
        $this->urlBuilder = $urlBuilder;
        $this->quoteIdMask = $quoteIdToMaskedQuoteId;
    }

    /**
     * Check `in_context` config value
     *
     * @return bool
     */
    private function isInContext(): bool
    {
        return (bool)(int) $this->config->getValue('in_context');
    }

    /**
     * Check `visible_on_cart` config value
     *
     * @return bool
     */
    private function isVisibleOnCart(): bool
    {
        return (bool)(int) $this->config->getValue('visible_on_cart');
    }

    /**
     * Check is Paypal In-Context Express Checkout button should render in cart/mini-cart
     *
     * @return bool
     */
    private function shouldRender(): bool
    {
        return $this->payment->isAvailable($this->session->getQuote())
            && $this->isInContext()
            && $this->isVisibleOnCart()
            && $this->getQuoteId()
            && !$this->getIsInCatalogProduct();
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
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * Returns string to initialize js component
     *
     * @return string
     */
    public function getJsInitParams(): string
    {
        $config = [];
        $quoteId = $this->getQuoteId();
        if (!empty($quoteId)) {
            $clientConfig = [
                'quoteId' => $quoteId,
                'customerId' => $this->session->getQuote()->getCustomerId(),
                'button' => 1,
                'getTokenUrl' => $this->urlBuilder->getUrl(
                    'paypal/express/getTokenData',
                    ['_secure' => $this->getRequest()->isSecure()]
                ),
                'onAuthorizeUrl' => $this->urlBuilder->getUrl(
                    'paypal/express/onAuthorization',
                    ['_secure' => $this->getRequest()->isSecure()]
                ),
                'onCancelUrl' => $this->urlBuilder->getUrl(
                    'paypal/express/cancel',
                    ['_secure' => $this->getRequest()->isSecure()]
                )
            ];
            $smartButtonsConfig = $this->getIsShoppingCart()
                ? $this->smartButtonConfig->getConfig('cart')
                : $this->smartButtonConfig->getConfig('mini_cart');
            $clientConfig = array_replace_recursive($clientConfig, $smartButtonsConfig);
            $config = [
                'Magento_Paypal/js/in-context/button' => [
                    'clientConfig' => $clientConfig
                ]
            ];
        }
        $json = $this->serializer->serialize($config);
        return $json;
    }

    /**
     * Returns container id
     *
     * @return string
     */
    public function getContainerId(): string
    {
        return $this->getData('button_id');
    }

    /**
     * Get quote id from session
     *
     * @return string
     */
    private function getQuoteId(): string
    {
        $quoteId = (int)$this->session->getQuoteId();
        if (!$this->session->getQuote()->getCustomerId()) {
            try {
                $quoteId = $this->quoteIdMask->execute($quoteId);
            } catch (NoSuchEntityException $e) {
                $quoteId = "";
            }
        }
        return (string)$quoteId;
    }
}
