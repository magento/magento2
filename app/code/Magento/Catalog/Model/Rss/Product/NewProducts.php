<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Rss\Product;

/**
 * Class NewProducts
 * @package Magento\Catalog\Model\Rss\Product
 */
class NewProducts
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $visibility;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->productFactory = $productFactory;
        $this->visibility = $visibility;
        $this->localeDate = $localeDate;
    }

    /**
     * @param int $storeId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductsCollection($storeId)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->productFactory->create();
        $todayStartOfDayDate = $this->localeDate->date()
            ->setTime(0, 0)
            ->format('Y-m-d H:i:s');

        $todayEndOfDayDate = $this->localeDate->date()
            ->setTime(23, 59, 59)
            ->format('Y-m-d H:i:s');
        /** @var $products \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $products = $product->getResourceCollection();
        $products->setStoreId($storeId);
        $products->addStoreFilter()->addAttributeToFilter(
            'news_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            'news_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter([
            ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
            ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
        ])->addAttributeToSort('news_from_date', 'desc')
        ->addAttributeToSelect(['name', 'short_description', 'description'], 'inner')
        ->addAttributeToSelect(
            [
                'price',
                'special_price',
                'special_from_date',
                'special_to_date',
                'msrp_display_actual_price_type',
                'msrp',
                'thumbnail',
            ],
            'left'
        )->applyFrontendPriceLimitations();
        $products->setVisibility($this->visibility->getVisibleInCatalogIds());

        return $products;
    }
}
