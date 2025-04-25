<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Block\Cart;

use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ObjectManager;

/**
 * Cart Shipping Block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Shipping extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @var CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var array|LayoutProcessorInterface[]
     */
    protected $layoutProcessors;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var JsonHexTag
     */
    private $jsonHexTagSerializer;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param CompositeConfigProvider $configProvider
     * @param array $layoutProcessors
     * @param array $data
     * @param Json|null $serializer
     * @param JsonHexTag|null $jsonHexTagSerializer
     * @throws \RuntimeException
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        CompositeConfigProvider $configProvider,
        array $layoutProcessors = [],
        array $data = [],
        ?Json $serializer = null,
        ?JsonHexTag $jsonHexTagSerializer = null
    ) {
        $this->configProvider = $configProvider;
        $this->layoutProcessors = $layoutProcessors;
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = true;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->jsonHexTagSerializer = $jsonHexTagSerializer ?: ObjectManager::getInstance()->get(JsonHexTag::class);
    }

    /**
     * Retrieve checkout configuration
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Retrieve serialized JS layout configuration ready to use in template
     *
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return $this->jsonHexTagSerializer->serialize($this->jsLayout);
    }

    /**
     * Get base url for block.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get Serialized Checkout Config
     *
     * @return bool|string
     * @since 100.2.0
     */
    public function getSerializedCheckoutConfig()
    {
        return $this->jsonHexTagSerializer->serialize($this->getCheckoutConfig());
    }
}
