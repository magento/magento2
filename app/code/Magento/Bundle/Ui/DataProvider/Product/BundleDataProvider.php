<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Ui\DataProvider\Product;

use Magento\Bundle\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\App\ObjectManager;

class BundleDataProvider extends ProductDataProvider
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Data $dataHelper
     * @param array $meta
     * @param array $data
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param StockStateInterface $stockState
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
        StockStateInterface $stockState = null
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
        $this->stockState = $stockState ?: ObjectManager::getInstance()->get(StockStateInterface::class);
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
            if (!is_array($item) || !array_key_exists('entity_id', $item)) {
                continue;
            }
            $items[$index]['selection_qty_is_integer'] = !$this->stockState->isStockQtyDecimal((int)$item['entity_id']);
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }
}
