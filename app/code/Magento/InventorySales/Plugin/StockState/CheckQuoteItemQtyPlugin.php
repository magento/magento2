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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventorySales\Model\IsProductSalableCondition\BackOrderNotifyCustomerCondition;
use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\ProductSalabilityError;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Replace legacy quote item check
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BackOrderNotifyCustomerCondition
     */
    private $backOrderNotifyCustomerCondition;

    /**
     * @param ObjectFactory $objectFactory
     * @param FormatInterface $format
     * @param IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param StockResolverInterface $stockResolver
     * @param StoreManagerInterface $storeManager
     * @param BackOrderNotifyCustomerCondition $backOrderNotifyCustomerCondition
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ObjectFactory $objectFactory,
        FormatInterface $format,
        IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StockResolverInterface $stockResolver,
        StoreManagerInterface $storeManager,
        BackOrderNotifyCustomerCondition $backOrderNotifyCustomerCondition
    ) {
        $this->objectFactory = $objectFactory;
        $this->format = $format;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->stockResolver = $stockResolver;
        $this->storeManager = $storeManager;
        $this->backOrderNotifyCustomerCondition = $backOrderNotifyCustomerCondition;
    }

    /**
     * Replace legacy quote item check
     *
     * @param StockStateInterface $subject
     * @param \Closure $proceed
     * @param int $productId
     * @param float $itemQty
     * @param float $qtyToCheck
     * @param float $origQty
     * @param int|null $scopeId
     *
     * @return DataObject
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCheckQuoteItemQty(
        StockStateInterface $subject,
        \Closure $proceed,
        $productId,
        $itemQty,
        $qtyToCheck,
        $origQty,
        $scopeId = null
    ) {
        $result = $this->objectFactory->create();
        $result->setHasError(false);

        $qty = $this->getNumber($itemQty);

        $skus = $this->getSkusByProductIds->execute([$productId]);
        $productSku = $skus[$productId];

        $websiteCode = $this->storeManager->getWebsite($scopeId)->getCode();
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = $stock->getStockId();

        $isSalableResult = $this->isProductSalableForRequestedQty->execute($productSku, (int)$stockId, $qty);

        if ($isSalableResult->isSalable() === false) {
            /** @var ProductSalabilityError $error */
            foreach ($isSalableResult->getErrors() as $error) {
                $result->setHasError(true)->setMessage($error->getMessage())->setQuoteMessage($error->getMessage())
                       ->setQuoteMessageIndex('qty');
            }
        }

        $productSalableResult = $this->backOrderNotifyCustomerCondition->execute($productSku, (int)$stockId, $qty);
        if ($productSalableResult->getErrors()) {
            /** @var ProductSalabilityError $error */
            foreach ($productSalableResult->getErrors() as $error) {
                $result->setMessage($error->getMessage());
            }
        }

        return $result;
    }

    /**
     * Convert quantity to a valid float
     *
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
