<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Catalog\Model\Resource\Product\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class GridDataProvider
 */
class ProductDataProvider extends \Magento\Ui\DataProvider\AbstractEavDataProvider
{
    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $addFieldStrategies,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
    }

    /**
     * Get collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected function getCollection()
    {
        return $this->collection;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => count($items),
            'items' => array_values($items),
        ];
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias = null);
        } else {
            $this->addFieldStrategies['default']->addField($this->getCollection(), $field, $alias = null);
        }
    }
}
