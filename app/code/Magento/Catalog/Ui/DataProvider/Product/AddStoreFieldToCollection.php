<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * @api
 * @since 2.0.0
 */
class AddStoreFieldToCollection implements AddFilterToCollectionInterface
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * Construct
     *
     * @param StoreManagerInterface $storeManager
     * @since 2.0.0
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (isset($condition['eq']) && $condition['eq']) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection  */
            $collection->addStoreFilter($this->storeManager->getStore($condition['eq']));
        }
    }
}
