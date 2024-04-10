<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\Websites;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Model\ResourceModel\Website\Collection as WebsiteCollection;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;

/**
 * Collection to fetch websites data at resolution time.
 */
class Collection implements ResetAfterRequestInterface
{
    /**
     * @var WebsiteCollection
     */
    private $websiteCollection;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var int[]
     */
    private $productIds = [];

    /**
     * @var array
     */
    private $websites = [];

    /**
     * @param WebsiteCollectionFactory $websiteCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        WebsiteCollectionFactory $websiteCollectionFactory,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->websiteCollection = $websiteCollectionFactory->create();
        $this->productCollection = $productCollectionFactory->create();
    }

    /**
     * Add product and id filter to filter for fetch.
     *
     * @param int $productId
     * @return void
     */
    public function addIdFilters(int $productId) : void
    {
        if (!in_array($productId, $this->productIds)) {
            $this->productIds[] = $productId;
        }
    }

    /**
     * Retrieve website for passed in product id.
     *
     * @param int $productId
     * @return array
     */
    public function getWebsiteForProductId(int $productId) : array
    {
        $websiteList = $this->fetch();

        if (!isset($websiteList[$productId])) {
            return [];
        }

        return $websiteList[$productId];
    }

    /**
     * Fetch website data and return in array format. Keys for links will be their product Ids.
     *
     * @return array
     */
    private function fetch() : array
    {
        if (empty($this->productIds) || !empty($this->websites)) {
            return $this->websites;
        }

        $selectUnique = $this->productCollection->getConnection()->select()->from(
            ['product_website' => $this->productCollection->getResource()->getTable('catalog_product_website')]
        )->where(
            'product_website.product_id IN (?)',
            $this->productIds
        )->where(
            'website_id > ?',
            0
        )->group('website_id');

        $websiteDataUnique = $this->productCollection->getConnection()->fetchAll($selectUnique);

        $websiteIds = [];
        foreach ($websiteDataUnique as $websiteData) {
            $websiteIds[] = $websiteData['website_id'];
        }
        $this->websiteCollection->addIdFilter($websiteIds);

        $siteData = $this->websiteCollection->getItems();

        $select = $this->productCollection->getConnection()->select()->from(
            ['product_website' => $this->productCollection->getResource()->getTable('catalog_product_website')]
        )->where(
            'product_website.product_id IN (?)',
            $this->productIds
        )->where(
            'website_id > ?',
            0
        );

        foreach ($this->productCollection->getConnection()->fetchAll($select) as $row) {
            $website = $siteData[$row['website_id']];
            $this->websites[$row['product_id']][$row['website_id']] = [
                'id' => $row['website_id'],
                'name' => $website->getData('name'),
                'code' => $website->getData('code'),
                'sort_order' => $website->getData('sort_order'),
                'default_group_id' => $website->getData('default_group_id'),
                'is_default' => $website->getData('is_default'),
            ];
        }
        return $this->websites;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->productIds = [];
        $this->websites = [];
    }
}
