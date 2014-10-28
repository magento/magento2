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
namespace Magento\Catalog\Model\Rss;

/**
 * Class Category
 * @package Magento\Catalog\Model\Rss
 */
class Category
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $visibility;

    /**
     * @param \Magento\Catalog\Model\Layer\Category $catalogLayer
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Category $catalogLayer,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Visibility $visibility
    ) {
        $this->catalogLayer = $catalogLayer;
        $this->collectionFactory = $collectionFactory;
        $this->visibility = $visibility;
    }


    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @return $this
     */
    public function getProductCollection(\Magento\Catalog\Model\Category $category, $storeId)
    {
        /** @var $layer \Magento\Catalog\Model\Layer */
        $layer = $this->catalogLayer->setStore($storeId);
        $collection = $category->getResourceCollection();
        $collection->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_anchor')
            ->addAttributeToFilter('is_active', 1)
            ->addIdFilter($category->getChildren())
            ->load();
        /** @var $productCollection \Magento\Catalog\Model\Resource\Product\Collection */
        $productCollection = $this->collectionFactory->create();

        $currentCategory = $layer->setCurrentCategory($category);
        $layer->prepareProductCollection($productCollection);
        $productCollection->addCountToCategories($collection);

        $category->getProductCollection()->setStoreId($storeId);

        $products = $currentCategory->getProductCollection()
            ->addAttributeToSort('updated_at', 'desc')
            ->setVisibility($this->visibility->getVisibleInCatalogIds())
            ->setCurPage(1)
            ->setPageSize(50);

        return $products;
    }
}
