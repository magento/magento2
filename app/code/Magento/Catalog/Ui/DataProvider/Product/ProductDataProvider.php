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
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $collection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return Store
     */
    protected function getStore()
    {
        $storeId = $this->request->getParam('store', 0);
        return $this->storeManager->getStore($storeId);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $store = $this->getStore();
        if ($store->getId()) {
            $this->collection->addStoreFilter($this->getStore());
        }
        $this->collection->load();
        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => count($items),
            'items' => array_values($items),
        ];
    }
}
