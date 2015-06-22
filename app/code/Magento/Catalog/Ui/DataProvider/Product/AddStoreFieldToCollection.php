<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

/**
 * Class AddFieldToCollection
 */
class AddStoreFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Request
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Construct
     *
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $store = $this->getStore();
        if ($store->getId()) {
            $collection->addStoreFilter($store);
        }
    }

    /**
     * Get store
     *
     * @return Store
     */
    protected function getStore()
    {
        return $this->storeManager->getStore($this->request->getParam('store', Store::DEFAULT_STORE_ID));
    }
}
