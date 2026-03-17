<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class AddFreeGiftToQuote
{
    private bool $isProcessing = false;

    public function __construct(
        private readonly RuleCollectionFactory $ruleCollectionFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger,
        private readonly CartRepositoryInterface $cartRepository
    ) {
    }

    public function addFreeGifts(Quote $quote): void
    {
        if ($this->isProcessing || $quote->getData('free_gifts_processed')) {
            return;
        }

        if (!$quote->getItemsCount()) {
            return;
        }

        $this->isProcessing = true;
        $quote->setData('free_gifts_processed', true);

        try {
            $websiteId = (int)$this->storeManager->getStore($quote->getStoreId())->getWebsiteId();
            $customerGroupId = (int)$quote->getCustomerGroupId();

            $rules = $this->ruleCollectionFactory->create()
                ->addFieldToFilter('simple_action', Rule::FREE_GIFT_ACTION)
                ->addFieldToFilter('is_active', 1)
                ->setValidationFilter($websiteId, $customerGroupId, $quote->getCouponCode() ?? '');

            $existingSkus = [];
            foreach ($quote->getAllItems() as $item) {
                $existingSkus[$item->getSku()] = true;
            }

            $giftsAdded = false;
            foreach ($rules as $rule) {
                $giftSku = $rule->getData('gift_sku');
                $giftQty = (int)($rule->getData('gift_qty') ?: 1);

                if (!$giftSku || isset($existingSkus[$giftSku])) {
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
                    continue;
                }

                $buyRequest = new DataObject(['qty' => $giftQty]);
                try {
                    $quote->setTotalsCollectedFlag(true);
                    $quote->addProduct($product, $buyRequest);
                    $quote->setTotalsCollectedFlag(false);
                    $existingSkus[$giftSku] = true;
                    $giftsAdded = true;
                } catch (LocalizedException $e) {
                    $quote->setTotalsCollectedFlag(false);
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

            if ($giftsAdded) {
                $this->cartRepository->save($quote);
                foreach ($quote->getAllAddresses() as $address) {
                    $address->unsetData('cached_items_all');
                    $address->unsetData('cached_items_nominal');
                    $address->unsetData('cached_items_nonnominal');
                }
            }
        } finally {
            $this->isProcessing = false;
        }
    }
}
