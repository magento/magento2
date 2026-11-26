<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Checkout\Block\Cart;

use Magento\Store\Model\ScopeInterface;

/**
 * Cart sidebar block
 *
 * @api
 * @since 100.0.2
 */
class Sidebar extends AbstractCart
{
    /**
     * Xml pah to checkout sidebar display value
     */
    public const XML_PATH_CHECKOUT_SIDEBAR_DISPLAY = 'checkout/sidebar/display';

    /**
     * Xml pah to checkout sidebar count value
     */
    public const XML_PATH_CHECKOUT_SIDEBAR_COUNT = 'checkout/sidebar/count';

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider,
        array $data = [],
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        if (isset($data['jsLayout'])) {
            $this->jsLayout = array_merge_recursive($jsLayoutDataProvider->getData(), $data['jsLayout']);
            unset($data['jsLayout']);
        } else {
            $this->jsLayout = $jsLayoutDataProvider->getData();
        }
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = false;
        $this->imageHelper = $imageHelper;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Get js layout.
     *
     * Overridden to dynamically copy item renderers and other key layout components
     * to any secondary minicarts declared under components.
     *
     * @return string
     */
    public function getJsLayout()
    {
        $jsLayout = $this->jsLayout;
        $minicartContent = $this->getMinicartContent($jsLayout);

        if ($minicartContent) {
            $jsLayout = $this->processMinicartComponents($jsLayout, $minicartContent);
        }

        return $this->serializer->serialize($jsLayout);
    }

    /**
     * Get minicart content configuration.
     *
     * @param array $jsLayout
     * @return array|null
     */
    private function getMinicartContent(array $jsLayout): ?array
    {
        if (isset($jsLayout['components']['minicart_content'])
            && isset($jsLayout['components']['minicart_content']['config']['itemRenderer'])
        ) {
            return $jsLayout['components']['minicart_content'];
        }

        $layout = $this->getLayout();
        $minicartBlock = $layout ? $layout->getBlock('minicart') : null;
        if ($minicartBlock && $minicartBlock !== $this) {
            $minicartJsLayout = $this->serializer->unserialize($minicartBlock->getJsLayout());
            if (isset($minicartJsLayout['components']['minicart_content'])) {
                return $minicartJsLayout['components']['minicart_content'];
            }
        }

        return null;
    }

    /**
     * Process and update secondary minicarts with primary minicart content.
     *
     * @param array $jsLayout
     * @param array $minicartContent
     * @return array
     */
    private function processMinicartComponents(array $jsLayout, array $minicartContent): array
    {
        if (!isset($jsLayout['components'])) {
            return $jsLayout;
        }

        foreach ($jsLayout['components'] as $name => &$component) {
            if ($name !== 'minicart_content'
                && isset($component['component'])
                && $component['component'] === 'Magento_Checkout/js/view/minicart'
            ) {
                $component = $this->updateComponentConfig($component, $minicartContent);
            }
        }

        return $jsLayout;
    }

    /**
     * Update component configuration and children with minicart content.
     *
     * @param array $component
     * @param array $minicartContent
     * @return array
     */
    private function updateComponentConfig(array $component, array $minicartContent): array
    {
        if (!isset($component['config'])) {
            $component['config'] = [];
        }
        if (!isset($component['config']['itemRenderer'])
            && isset($minicartContent['config']['itemRenderer'])
        ) {
            $component['config']['itemRenderer'] = $minicartContent['config']['itemRenderer'];
        }
        if (!isset($component['children'])) {
            $component['children'] = [];
        }
        if (!isset($component['children']['item.renderer'])
            && isset($minicartContent['children']['item.renderer'])
        ) {
            $component['children']['item.renderer'] = $minicartContent['children']['item.renderer'];
        }
        if (!isset($component['children']['subtotal.container'])
            && isset($minicartContent['children']['subtotal.container'])
        ) {
            $component['children']['subtotal.container'] =
                $minicartContent['children']['subtotal.container'];
        }

        return $component;
    }

    /**
     * Returns minicart config
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'shoppingCartUrl' => $this->getShoppingCartUrl(),
            'checkoutUrl' => $this->getCheckoutUrl(),
            'updateItemQtyUrl' => $this->getUpdateItemQtyUrl(),
            'removeItemUrl' => $this->getRemoveItemUrl(),
            'imageTemplate' => $this->getImageHtmlTemplate(),
            'baseUrl' => $this->getBaseUrl(),
            'minicartMaxItemsVisible' => $this->getMiniCartMaxItemsCount(),
            'websiteId' => $this->_storeManager->getStore()->getWebsiteId(),
            'maxItemsToDisplay' => $this->getMaxItemsToDisplay(),
            'storeId' => $this->_storeManager->getStore()->getId(),
            'storeGroupId' => $this->_storeManager->getStore()->getStoreGroupId()
        ];
    }

    /**
     * Get serialized config
     *
     * @return string
     * @since 100.2.0
     */
    public function getSerializedConfig()
    {
        return $this->serializer->serialize($this->getConfig());
    }

    /**
     * Get image html template
     *
     * @return string
     */
    public function getImageHtmlTemplate()
    {
        return 'Magento_Catalog/product/image_with_borders';
    }

    /**
     * Get one page checkout page url
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout');
    }

    /**
     * Get shopping cart page url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getShoppingCartUrl()
    {
        return $this->getUrl('checkout/cart');
    }

    /**
     * Get update cart item url
     *
     * @return string
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function getUpdateItemQtyUrl()
    {
        return $this->getUrl('checkout/sidebar/updateItemQty', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Get remove cart item url
     *
     * @return string
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function getRemoveItemUrl()
    {
        return $this->getUrl('checkout/sidebar/removeItem', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Define if Mini Shopping Cart Pop-Up Menu enabled
     *
     * @return bool
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsNeedToDisplaySideBar()
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_CHECKOUT_SIDEBAR_DISPLAY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return totals from custom quote if needed
     *
     * @return array
     */
    public function getTotalsCache()
    {
        if (empty($this->_totals)) {
            $quote = $this->getCustomQuote() ? $this->getCustomQuote() : $this->getQuote();
            $this->_totals = $quote->getTotals();
        }
        return $this->_totals;
    }

    /**
     * Retrieve subtotal block html
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getTotalsHtml()
    {
        return $this->getLayout()->getBlock('checkout.cart.minicart.totals')->toHtml();
    }

    /**
     * Return base url.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Return max visible item count for minicart
     *
     * @return int
     */
    private function getMiniCartMaxItemsCount()
    {
        return (int)$this->_scopeConfig->getValue('checkout/sidebar/count', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Returns maximum cart items to display
     *
     * This setting regulates how many items will be displayed in minicart
     *
     * @return int
     */
    private function getMaxItemsToDisplay()
    {
        return (int)$this->_scopeConfig->getValue(
            'checkout/sidebar/max_items_display_count',
            ScopeInterface::SCOPE_STORE
        );
    }
}
