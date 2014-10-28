<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Service\V1;

use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Service related to Product Stock Status
 */
class StockStatusService implements StockStatusServiceInterface
{
    /**
     * @var Status
     */
    protected $stockStatus;

    /**
     * @var \Magento\Store\Model\Resolver\Website
     */
    protected $scopeResolver;

    /**
     * @var \Magento\Catalog\Service\V1\Product\ProductLoader
     */
    protected $productLoader;

    /**
     * @var StockItemService
     */
    protected $stockItemService;

    /**
     * @var Data\StockStatusBuilder
     */
    protected $stockStatusBuilder;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\Status\CollectionFactory
     */
    protected $itemsFactory;

    /**
     * @var Data\LowStockResultBuilder
     */
    protected $lowStockResultBuilder;

    /**
     * @param Status $stockStatus
     * @param StockItemService $stockItemService
     * @param \Magento\Catalog\Service\V1\Product\ProductLoader $productLoader
     * @param \Magento\Store\Model\Resolver\Website $scopeResolver
     * @param Data\StockStatusBuilder $stockStatusBuilder
     * @param \Magento\CatalogInventory\Model\Resource\Stock\Status\CollectionFactory $itemsFactory
     * @param Data\LowStockResultBuilder $lowStockResultBuilder
     */
    public function __construct(
        Status $stockStatus,
        \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService,
        \Magento\Catalog\Service\V1\Product\ProductLoader $productLoader,
        \Magento\Store\Model\Resolver\Website $scopeResolver,
        Data\StockStatusBuilder $stockStatusBuilder,
        \Magento\CatalogInventory\Model\Resource\Stock\Status\CollectionFactory $itemsFactory,
        Data\LowStockResultBuilder $lowStockResultBuilder
    ) {
        $this->stockStatus = $stockStatus;
        $this->stockItemService = $stockItemService;
        $this->productLoader = $productLoader;
        $this->scopeResolver = $scopeResolver;
        $this->stockStatusBuilder = $stockStatusBuilder;
        $this->itemsFactory = $itemsFactory;
        $this->lowStockResultBuilder = $lowStockResultBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductStockStatus($productId, $websiteId, $stockId = Stock::DEFAULT_STOCK_ID)
    {
        $stockStatusData = $this->stockStatus->getProductStockStatus([$productId], $websiteId, $stockId);
        $stockStatus = empty($stockStatusData[$productId]) ? null : $stockStatusData[$productId];

        return $stockStatus;
    }

    /**
     * Assign Stock Status to Product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $stockId
     * @param int $stockStatus
     * @return \Magento\CatalogInventory\Service\V1\StockStatusService
     */
    public function assignProduct(
        \Magento\Catalog\Model\Product $product,
        $stockId = Stock::DEFAULT_STOCK_ID,
        $stockStatus = null
    ) {
        $this->stockStatus->assignProduct($product, $stockId, $stockStatus);
        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function getProductStockStatusBySku($sku)
    {
        $product = $this->productLoader->load($sku);
        $productId = $product->getId();
        if (!$productId) {
            throw new NoSuchEntityException("Product with SKU \"{$sku}\" does not exist");
        }

        $data = $this->stockStatus->getProductStockStatus(
            [$productId],
            $this->scopeResolver->getScope()->getId()
        );
        $stockStatus = (bool)$data[$productId];

        $result = [
            Data\StockStatus::STOCK_STATUS => $stockStatus,
            Data\StockStatus::STOCK_QTY => $this->stockItemService->getStockQty($productId)
        ];

        $this->stockStatusBuilder->populateWithArray($result);

        return $this->stockStatusBuilder->create();
    }

    /**
     * Retrieves a list of SKU's with low inventory qty
     *
     * {@inheritdoc}
     */
    public function getLowStockItems($lowStockCriteria)
    {
        /** @var \Magento\CatalogInventory\Model\Resource\Stock\Status\Collection $itemCollection */
        $itemCollection = $this->itemsFactory->create();
        $itemCollection->addWebsiteFilter($this->scopeResolver->getScope());
        $itemCollection->addQtyFilter($lowStockCriteria->getQty());
        $itemCollection->setCurPage($lowStockCriteria->getCurrentPage());
        $itemCollection->setPageSize($lowStockCriteria->getPageSize());

        $countOfItems = $itemCollection->getSize();
        $listOfSku = [];
        foreach ($itemCollection as $item) {
            $listOfSku[] = $item->getSku();
        }

        $this->lowStockResultBuilder->setSearchCriteria($lowStockCriteria);
        $this->lowStockResultBuilder->setTotalCount($countOfItems);
        $this->lowStockResultBuilder->setItems($listOfSku);
        return $this->lowStockResultBuilder->create();
    }
}
