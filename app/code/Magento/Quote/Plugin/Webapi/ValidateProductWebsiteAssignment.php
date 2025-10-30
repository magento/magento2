<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Plugin\Webapi;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link as ProductWebsiteLink;

/**
 * Plugin to validate product website assignment for REST API cart operations
 */
class ValidateProductWebsiteAssignment
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param CartRepositoryInterface $cartRepository
     * @param ProductResource $productResource
     * @param ProductWebsiteLink $productWebsiteLink
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly ProductResource $productResource,
        private readonly ProductWebsiteLink $productWebsiteLink
    ) {
    }

    /**
     * Validate product website assignment before saving cart item
     *
     * @param CartItemRepositoryInterface $subject
     * @param CartItemInterface $cartItem
     * @return void
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        CartItemRepositoryInterface $subject,
        CartItemInterface $cartItem
    ): void {
        $sku = $cartItem->getSku();
        if (!$sku) {
            return;
        }

        // Skip validation on update; website was already validated on add
        if ($cartItem->getItemId()) {
            return;
        }

        try {
            $quote = $this->cartRepository->getActive($cartItem->getQuoteId());
            $this->checkProductWebsiteAssignmentBySku($sku, $quote->getStoreId());

        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Product that you are trying to add is not available.'));
        }
    }

    /**
     * Check product website assignment by SKU
     *
     * @param string $sku
     * @param int $storeId
     * @throws LocalizedException
     */
    private function checkProductWebsiteAssignmentBySku(string $sku, int $storeId): void
    {
        $productId = (int)$this->productResource->getIdBySku($sku);
        if (!$productId) {
            throw new LocalizedException(__('Product that you are trying to add is not available.'));
        }
        $websiteIds = $this->productWebsiteLink->getWebsiteIdsByProductId($productId);
        if (empty($websiteIds)) {
            return;
        }
        $this->validateWebsiteAssignment($websiteIds, $storeId);
    }

    /**
     * Validate product website assignment
     *
     * @param array|null $websiteIds
     * @param int $storeId
     * @throws LocalizedException
     */
    private function validateWebsiteAssignment(?array $websiteIds, int $storeId): void
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        if (empty($websiteIds) || !in_array($websiteId, $websiteIds, true)) {
            throw new LocalizedException(__('Product that you are trying to add is not available.'));
        }
    }
}
