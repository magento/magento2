<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Quote\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link as ProductWebsiteLink;

/**
 * Plugin to update cart ID from request and validate product website assignment
 */
class UpdateCartId
{
    /**
     * @param RestRequest $request
     * @param StoreManagerInterface $storeManager
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @param ProductResource $productResource
     * @param ProductWebsiteLink $productWebsiteLink
     */
    public function __construct(
        private readonly RestRequest $request,
        private readonly StoreManagerInterface $storeManager,
        private readonly QuoteIdMaskFactory $quoteIdMaskFactory,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly ProductResource $productResource,
        private readonly ProductWebsiteLink $productWebsiteLink
    ) {
    }

    /**
     * Before saving a guest cart item, set quote ID from request and validate website assignment
     *
     * @param GuestCartItemRepositoryInterface $subject
     * @param CartItemInterface $cartItem
     * @return void
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        GuestCartItemRepositoryInterface $subject,
        CartItemInterface $cartItem
    ): void {
        if ($cartId = $this->request->getParam('cartId')) {
            $cartItem->setQuoteId($cartId);
        }

        $this->validateProductWebsiteAssignment($cartItem);
    }

    /**
     * Validate product's website assignment for guest cart item
     *
     * @param CartItemInterface $cartItem
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function validateProductWebsiteAssignment(CartItemInterface $cartItem): void
    {
        $sku = $cartItem->getSku();
        if (!$sku) {
            return;
        }

        // Skip validation on update; website was already validated on add
        if ($cartItem->getItemId()) {
            return;
        }

        $storeId = (int)($cartItem->getStoreId() ?? 0);

        if (!$storeId) {
            try {
                $storeId = (int)$this->storeManager->getStore()->getId();
            } catch (\Throwable $e) {
                $storeId = 0;
            }
        }

        if (!$storeId) {
            try {
                $maskedQuoteId = $cartItem->getQuoteId();
                if ($maskedQuoteId) {
                    $quoteIdMask = $this->quoteIdMaskFactory->create()->load($maskedQuoteId, 'masked_id');
                    $quoteId = (int)$quoteIdMask->getQuoteId();
                    if ($quoteId) {
                        $storeId = (int)$this->cartRepository->get($quoteId)->getStoreId();
                    }
                }
            } catch (NoSuchEntityException) {
                throw new LocalizedException(__('Product that you are trying to add is not available.'));
            }
        }

        if ($storeId) {
            $this->validateWebsiteAssignmentBySku($sku, $storeId);
        }
    }

    /**
     * Validate by SKU for new items
     *
     * @param string $sku
     * @param int $storeId
     * @return void
     * @throws LocalizedException
     */
    private function validateWebsiteAssignmentBySku(string $sku, int $storeId): void
    {
        $productId = (int)$this->productResource->getIdBySku($sku);
        if (!$productId) {
            throw new LocalizedException(__('Product that you are trying to add is not available.'));
        }
        $websiteIds = $this->productWebsiteLink->getWebsiteIdsByProductId($productId);
        if (empty($websiteIds)) {
            return;
        }
        $this->checkProductInWebsite($websiteIds, $storeId);
    }

    /**
     * Validate by product ID for existing items
     *
     * @param array|null $websiteIds
     * @param int $storeId
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function checkProductInWebsite(?array $websiteIds, int $storeId): void
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        if (empty($websiteIds) || !in_array($websiteId, $websiteIds, true)) {
            throw new LocalizedException(__('Product that you are trying to add is not available.'));
        }
    }
}
