<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Block;

use Magento\Cms\Model\Resource\Block\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Cms\Model\Resource\Block\Collection
     */
    protected $collection;

    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param FilterPool $filterPool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        FilterPool $filterPool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->filterPool = $filterPool;
    }

    /**
     * @return \Magento\Cms\Model\Resource\Block\Collection
     */
    protected function getCollection()
    {
        return $this->collection;
    }

    /**
     * @inheritdoc
     */
    public function addFilter($condition, $field = null, $type = 'regular')
    {
        $this->filterPool->registerNewFilter($condition, $field, $type);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $this->filterPool->applyFilters($this->collection);
        return $this->collection->toArray();
    }

    /**
     * Retrieve count of loaded items
     *
     * @return int
     */
    public function count()
    {
        $this->filterPool->applyFilters($this->collection);
        return $this->collection->count();
    }
}
