<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Bundle\Helper\Data;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class BundleDataProvider extends ProductDataProvider
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Data $dataHelper
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Data $dataHelper,
        array $meta = [],
        array $data = [],
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        StockRegistryInterface $stockRegistry = null
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->dataHelper = $dataHelper;
        $this->stockRegistry = $stockRegistry
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(StockRegistryInterface::class);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->addAttributeToFilter(
                'type_id',
                $this->dataHelper->getAllowedSelectionTypes()
            );
            $this->getCollection()->addFilterByRequiredOptions();
            $this->getCollection()->addStoreFilter(
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            );
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();

        foreach ($items as $index => $item) {
            $items[$index]['selection_qty_is_integer'] = !$this->isProductQtyDecimal($item['entity_id']);
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }

    /**
     * @param int $productId
     * @return bool
     */
    private function isProductQtyDecimal(int $productId): bool
    {
        try {
            $productStock = $this->stockRegistry->getStockItem($productId);
            $result = $productStock->getIsQtyDecimal();
        } catch (NoSuchEntityException $e) {
            $result = false;
        }

        return $result;
    }
}
