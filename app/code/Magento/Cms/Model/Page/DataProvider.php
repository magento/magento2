<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Page;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    protected $collection;

    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $pageCollectionFactory
     * @param FilterPool $filterPool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $pageCollectionFactory,
        FilterPool $filterPool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $pageCollectionFactory->create();
        $this->filterPool = $filterPool;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Magento\Cms\Model\Page $page */
        foreach ($items as $page) {
            $this->loadedData[$page->getId()] = $page->getData();
        }

        return $this->loadedData;
    }
}
