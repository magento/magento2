<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\UrlInterface;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\Data\Form\FormKey;
use Magento\Catalog\Model\Product\Attribute\Source\Boolean;

/**
 * Configuration provider for GiftMessage rendering on "Checkout cart" page.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class GiftMessageConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfiguration;

    /**
     * @var \Magento\GiftMessage\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\GiftMessage\Api\ItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LocaleFormat
     */
    protected $localeFormat;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\GiftMessage\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\GiftMessage\Api\ItemRepositoryInterface $itemRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param HttpContext $httpContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param LocaleFormat $localeFormat
     * @param FormKey $formKey
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\GiftMessage\Api\CartRepositoryInterface $cartRepository,
        \Magento\GiftMessage\Api\ItemRepositoryInterface $itemRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        HttpContext $httpContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        LocaleFormat $localeFormat,
        FormKey $formKey
    ) {
        $this->scopeConfiguration = $context->getScopeConfig();
        $this->cartRepository = $cartRepository;
        $this->itemRepository = $itemRepository;
        $this->checkoutSession = $checkoutSession;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
        $this->localeFormat = $localeFormat;
        $this->formKey = $formKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $configuration = [];
        $configuration['giftMessage'] = [];
        $orderLevelGiftMessageConfiguration = (bool)$this->scopeConfiguration->getValue(
            GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $itemLevelGiftMessageConfiguration = (bool)$this->scopeConfiguration->getValue(
            GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($orderLevelGiftMessageConfiguration) {
            $orderMessages = $this->getOrderLevelGiftMessages();
            $configuration['isOrderLevelGiftOptionsEnabled'] = (bool)$this->isQuoteVirtual() ? false : true;
            $configuration['giftMessage']['orderLevel'] = $orderMessages === null ? true : $orderMessages->getData();
        }

        $itemMessages = $this->getItemLevelGiftMessages();
        $configuration['isItemLevelGiftOptionsEnabled'] = $itemLevelGiftMessageConfiguration;
        $configuration['giftMessage']['itemLevel'] = $itemMessages === null ? true : $itemMessages;

        $configuration['priceFormat'] = $this->localeFormat->getPriceFormat(
            null,
            $this->checkoutSession->getQuote()->getQuoteCurrencyCode()
        );
        $configuration['storeCode'] = $this->getStoreCode();
        $configuration['isCustomerLoggedIn'] = $this->isCustomerLoggedIn();
        $configuration['formKey'] = $this->formKey->getFormKey();
        $store = $this->storeManager->getStore();
        $configuration['baseUrl'] = $store->isFrontUrlSecure()
                ? $store->getBaseUrl(UrlInterface::URL_TYPE_LINK, true)
                : $store->getBaseUrl(UrlInterface::URL_TYPE_LINK, false);
        return $configuration;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    private function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Retrieve store code
     *
     * @return string
     */
    protected function getStoreCode()
    {
        return $this->checkoutSession->getQuote()->getStore()->getCode();
    }

    /**
     * Check if quote is virtual
     *
     * @return bool
     */
    protected function isQuoteVirtual()
    {
        return $this->checkoutSession->loadCustomerQuote()->getQuote()->getIsVirtual();
    }

    /**
     * Load already specified quote level gift message.
     *
     * @return \Magento\GiftMessage\Api\Data\MessageInterface|null
     */
    protected function getOrderLevelGiftMessages()
    {
        $cartId = $this->checkoutSession->getQuoteId();
        return $this->cartRepository->get($cartId);
    }

    /**
     * Load already specified item level gift messages and related configuration.
     *
     * @return \Magento\GiftMessage\Api\Data\MessageInterface[]|null
     */
    protected function getItemLevelGiftMessages()
    {
        $itemLevelConfig = [];
        $quote = $this->checkoutSession->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            $itemId = $item->getId();
            $itemLevelConfig[$itemId] = [];
            $isMessageAvailable = $item->getProduct()->getGiftMessageAvailable();
            // use gift message product setting if it is available
            if ($isMessageAvailable !== null && $isMessageAvailable != Boolean::VALUE_USE_CONFIG) {
                $itemLevelConfig[$itemId]['is_available'] = (bool)$isMessageAvailable;
            }
            $message = $this->itemRepository->get($quote->getId(), $itemId);
            if ($message) {
                $itemLevelConfig[$itemId]['message'] = $message->getData();
            }
        }
        return count($itemLevelConfig) === 0 ? null : $itemLevelConfig;
    }
}
