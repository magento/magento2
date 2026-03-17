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
            /** @var Quote $quote */
            $quote = $observer->getEvent()->getQuote();
            if (!$quote) {
                return;
            }

            $allItems = $quote->getAllVisibleItems();
            if (empty($allItems)) {
                return;
            }

            $websiteId = (int)$this->storeManager->getStore($quote->getStoreId())->getWebsiteId();
            $customerGroupId = (int)$quote->getCustomerGroupId();
            $couponCode = $quote->getCouponCode() ?? '';

            $rules = $this->ruleCollectionFactory->create()
                ->setValidationFilter($websiteId, $customerGroupId, $couponCode);

            $existingSkus = [];
            foreach ($quote->getAllItems() as $item) {
                $existingSkus[$item->getSku()] = true;
            }

            foreach ($rules as $rule) {
                if ($rule->getSimpleAction() !== Rule::FREE_GIFT_ACTION) {
                    continue;
                }

                $giftSku = $rule->getData('gift_sku');
                $giftQty = (int)($rule->getData('gift_qty') ?: 1);

                if (!$giftSku || isset($existingSkus[$giftSku])) {
                    continue;
                }

                $address = $quote->isVirtual()
                    ? $quote->getBillingAddress()
                    : $quote->getShippingAddress();
                $address->unsetData('cached_items_all');
                if (!$rule->validate($address)) {
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
                    $quote->addProduct($product, $buyRequest);
                    $existingSkus[$giftSku] = true;
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
