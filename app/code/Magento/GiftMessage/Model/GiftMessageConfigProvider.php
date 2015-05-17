<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;

/**
 * Configuration provider for GiftMessage rendering on "Shipping Method" step of checkout.
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
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\GiftMessage\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\GiftMessage\Api\ItemRepositoryInterface $itemRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\GiftMessage\Api\CartRepositoryInterface $cartRepository,
        \Magento\GiftMessage\Api\ItemRepositoryInterface $itemRepository,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->scopeConfiguration = $context->getScopeConfig();
        $this->cartRepository = $cartRepository;
        $this->itemRepository = $itemRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $configuration = [];
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
            $configuration['isOrderLevelGiftOptionsEnabled'] = true;
            $configuration['giftMessage']['orderLevel'] = $orderMessages === null ? true : $orderMessages->getData();
        }
        if ($itemLevelGiftMessageConfiguration) {
            $itemMessages = $this->getItemLevelGiftMessages();
            $configuration['isItemLevelGiftOptionsEnabled'] = true;
            $configuration['giftMessage']['itemLevel'] = $itemMessages === null ? true : $itemMessages;
        }
        return $configuration;
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
     * Load already specified item level gift messages.
     *
     * @return \Magento\GiftMessage\Api\Data\MessageInterface[]|null
     */
    protected function getItemLevelGiftMessages()
    {
        $itemMessages = [];
        $cartId = $this->checkoutSession->getQuoteId();
        $items = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($items as $item) {
            $itemId = $item->getId();
            $message = $this->itemRepository->get($cartId, $itemId);
            if ($message) {
                $itemMessages[$itemId] = $message->getData();
            }
        }
        return count($itemMessages) === 0 ? null : $itemMessages;
    }
}
