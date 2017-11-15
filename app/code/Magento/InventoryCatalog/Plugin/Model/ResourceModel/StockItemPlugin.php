<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\Model\ResourceModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item\Interceptor;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Model\DefaultSourceProvider;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Class provide Around Plugin on Stock::save to migrate single stock data
 */
class StockItemPlugin
{
    /**
     * @var SourceItemInterface
     */
    private $sourceItem;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var DefaultSourceProvider
     */
    private $defaultSourceProvider;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param SourceItemInterface $sourceItem
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param DefaultSourceProvider $defaultSourceProvider
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        SourceItemInterface $sourceItem,
        SourceItemsSaveInterface $sourceItemsSave,
        DefaultSourceProvider $defaultSourceProvider,
        ProductRepositoryInterface $productRepository
    ) {
        $this->sourceItem = $sourceItem;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Interceptor $subject
     * @param callable $proceed
     * @param Item $stockItem
     *
     * @return AbstractDb
     * @throws \Exception
     * @throws AlreadyExistsException;
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(Interceptor $subject, callable $proceed, Item $stockItem): AbstractDb
    {
        $product = $this->productRepository->getById($stockItem->getProductId());
        $this->sourceItem->setSourceId($this->defaultSourceProvider->getId());
        $this->sourceItem->setSku($product->getSku());
        $this->sourceItem->setQuantity($stockItem->getQty());
        $this->sourceItem->setStatus((int)$stockItem->getIsInStock());
        $this->sourceItemsSave->execute([$this->sourceItem]);

        return $proceed($stockItem);
    }
}
