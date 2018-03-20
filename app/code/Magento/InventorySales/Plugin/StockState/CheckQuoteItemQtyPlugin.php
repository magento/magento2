<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\StockState;

use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\ProductSalabilityError;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;

class CheckQuoteItemQtyPlugin
{
    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var FormatInterface
     */
    private $format;

    /**
     * @var IsProductSalableForRequestedQtyInterface
     */
    private $isProductSalableForRequestedQty;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param ObjectFactory $objectFactory
     * @param FormatInterface $format
     * @param IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        ObjectFactory $objectFactory,
        FormatInterface $format,
        IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->objectFactory = $objectFactory;
        $this->format = $format;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @param StockStateInterface $subject
     * @param \Closure $proceed
     * @param int $productId
     * @param float $itemQty
     * @param float $qtyToCheck
     * @param float $origQty
     * @param int|null $scopeId
     *
     * @return DataObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCheckQuoteItemQty(
        StockStateInterface $subject,
        \Closure $proceed,
        $productId,
        $itemQty,
        $qtyToCheck,
        $origQty,
        $scopeId
    ) {
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        if ($this->defaultStockProvider->getId() === $stockId) {
            $result = $proceed($productId, $itemQty, $qtyToCheck, $origQty, $scopeId);
        } else {
            $result = $this->objectFactory->create();
            $result->setHasError(false);

            $qty = $this->getNumber($qtyToCheck);

            $skus = $this->getSkusByProductIds->execute([$productId]);
            $productSku = $skus[$productId];

            $isSalableResult = $this->isProductSalableForRequestedQty->execute($productSku, $stockId, $qty);

            if ($isSalableResult->isSalable() === false) {
                /** @var ProductSalabilityError $error */
                foreach ($isSalableResult->getErrors() as $error) {
                    $result->setHasError(true)->setMessage($error->getMessage())->setQuoteMessage($error->getMessage())
                        ->setQuoteMessageIndex('qty');
                }
            }
        }

        return $result;
    }

    /**
     * @param string|float|int|null $qty
     *
     * @return float|null
     */
    private function getNumber($qty)
    {
        if (!is_numeric($qty)) {
            return $this->format->getNumber($qty);
        }

        return $qty;
    }
}
