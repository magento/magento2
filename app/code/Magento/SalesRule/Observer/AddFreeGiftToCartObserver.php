<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class AddFreeGiftToCartObserver implements ObserverInterface
{
    private bool $isProcessing = false;

    public function __construct(
        private readonly RuleCollectionFactory $ruleCollectionFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer): void
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            $quoteItem = $observer->getEvent()->getQuoteItem();
            $this->logger->info('FreeGift: observer fired, quoteItem=' . ($quoteItem ? $quoteItem->getSku() : 'null'));
            if (!$quoteItem) {
                return;
            }

            /** @var Quote $quote */
            $quote = $quoteItem->getQuote();
            $allItems = $quote ? $quote->getAllVisibleItems() : [];
            $this->logger->info('FreeGift: quote=' . ($quote ? $quote->getId() : 'null') . ', allVisibleItems=' . count($allItems));
            if (!$quote || empty($allItems)) {
                return;
            }

            $websiteId = (int)$this->storeManager->getStore($quote->getStoreId())->getWebsiteId();
            $customerGroupId = (int)$quote->getCustomerGroupId();
            $couponCode = $quote->getCouponCode() ?? '';
            $this->logger->info("FreeGift: websiteId=$websiteId, customerGroupId=$customerGroupId, coupon=$couponCode");

            $rules = $this->ruleCollectionFactory->create()
                ->setValidationFilter($websiteId, $customerGroupId, $couponCode);

            $this->logger->info('FreeGift: found ' . $rules->getSize() . ' rules before filtering');

            $existingSkus = [];
            foreach ($quote->getAllItems() as $item) {
                $existingSkus[$item->getSku()] = true;
            }
            $this->logger->info('FreeGift: existing SKUs in cart: ' . implode(', ', array_keys($existingSkus)));

            foreach ($rules as $rule) {
                /** @var Rule $rule */
                if ($rule->getSimpleAction() !== Rule::FREE_GIFT_ACTION) {
                    continue;
                }

                $giftSku = $rule->getData('gift_sku');
                $giftQty = (int)($rule->getData('gift_qty') ?: 1);
                $this->logger->info("FreeGift: rule {$rule->getRuleId()}, giftSku=$giftSku, giftQty=$giftQty");

                if (!$giftSku || isset($existingSkus[$giftSku])) {
                    $this->logger->info("FreeGift: skipped - empty sku or already in cart");
                    continue;
                }

                $address = $quote->isVirtual()
                    ? $quote->getBillingAddress()
                    : $quote->getShippingAddress();
                $address->unsetData('cached_items_all');
                $addressItems = $address->getAllItems();
                $this->logger->info('FreeGift: address items count=' . count($addressItems));
                $validated = $rule->validate($address);
                $this->logger->info("FreeGift: rule->validate() = " . ($validated ? 'true' : 'false'));
                if (!$validated) {
                    continue;
                }

                try {
                    $product = $this->productRepository->get($giftSku);
                } catch (NoSuchEntityException $e) {
                    $this->logger->warning(
                        sprintf('Free gift rule %s: product SKU "%s" not found.', $rule->getRuleId(), $giftSku)
                    );
                    continue;
                }

                if (!$product->isSalable()) {
                    $this->logger->info("FreeGift: product $giftSku is not salable");
                    continue;
                }

                $buyRequest = new DataObject(['qty' => $giftQty]);
                try {
                    $quote->addProduct($product, $buyRequest);
                    $existingSkus[$giftSku] = true;
                    $this->logger->info("FreeGift: successfully added $giftSku to quote");
                } catch (LocalizedException $e) {
                    $this->logger->warning(
                        sprintf(
                            'Free gift rule %s: could not add SKU "%s": %s',
                            $rule->getRuleId(),
                            $giftSku,
                            $e->getMessage()
                        )
                    );
                }
            }
        } finally {
            $this->isProcessing = false;
        }
    }
}
