<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Block\Express\InContext;

use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Paypal\Model\SmartButtonConfig;
use Magento\Framework\UrlInterface;

/**
 * Class Button
 */
class SmartButton extends Template implements ShortcutInterface
{
    private const ALIAS_ELEMENT_INDEX = 'alias';

    /**
     * @var Config
     */
    private $config;

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
     * @param Context $context
     * @param ConfigFactory $configFactory
     * @param SerializerInterface $serializer
     * @param SmartButtonConfig $smartButtonConfig
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigFactory $configFactory,
        SerializerInterface $serializer,
        SmartButtonConfig $smartButtonConfig,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $configFactory->create();
        $this->config->setMethod(Config::METHOD_EXPRESS);
        $this->serializer = $serializer;
        $this->smartButtonConfig = $smartButtonConfig;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Check is Paypal In-Context Express Checkout button should render in cart/mini-cart
     *
     * @return bool
     */
    private function shouldRender(): bool
    {
        $isInCatalog = $this->getIsInCatalogProduct();
        $isInContext = (bool)(int) $this->config->getValue('in_context');

        return ($isInContext && $isInCatalog);
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
        $clientConfig = [
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
        $smartButtonsConfig = $this->smartButtonConfig->getConfig('product');
        $clientConfig = array_replace_recursive($clientConfig, $smartButtonsConfig);
        $config = [
            'Magento_Paypal/js/in-context/product-express-checkout' => [
                'clientConfig' => $clientConfig
            ]
        ];

        return $this->serializer->serialize($config);
    }
}
