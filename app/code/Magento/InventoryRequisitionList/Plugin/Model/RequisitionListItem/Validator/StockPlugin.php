<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryRequisitionList\Plugin\Model\RequisitionListItem\Validator;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Model\RequisitionListItem\Validator\Stock;

/**
 * This plugin adds multi-source stock calculation capabilities to the Requisition List feature.
 */
class StockPlugin
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteId;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @var IsProductSalableForRequestedQtyInterface
     */
    private $isProductSalableForRequestedQty;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteId
     * @param IsProductSalableInterface $isProductSalable
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockByWebsiteIdResolverInterface $stockByWebsiteId,
        IsProductSalableInterface $isProductSalable,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty
    ) {
        $this->productRepository = $productRepository;
        $this->stockByWebsiteId = $stockByWebsiteId;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->isProductSalable = $isProductSalable;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
    }

    /**
     * Extend requisition list item stock validation with multi-sourcing capabilities.
     *
     * @param Stock $subject
     * @param callable $proceed
     * @param RequisitionListItemInterface $item
     * @return array Item errors
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundValidate(Stock $subject, callable $proceed, RequisitionListItemInterface $item)
    {
        $errors = [];
        $product = $this->productRepository->get($item->getSku(), false, null, true);

        if (!$this->isSourceItemManagementAllowedForProductType->execute($product->getTypeId())) {
            return $proceed($item);
        }

        $websiteId = (int)$product->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteId->execute($websiteId)->getStockId();
        $isSalable = $this->isProductSalable->execute($product->getSku(), $stockId);

        if (!$isSalable) {
            $errors[$subject::ERROR_OUT_OF_STOCK] = __('The SKU is out of stock.');
            return $errors;
        }
        $productSalableResult = $this->isProductSalableForRequestedQty->execute(
            $product->getSku(),
            $stockId,
            (float)$item->getQty()
        );
        if (!$productSalableResult->isSalable() && !$product->isComposite()) {
            $errors[$subject::ERROR_LOW_QUANTITY] = __('The requested qty is not available');
            return $errors;
        }

        return $errors;
    }
}
