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
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    public function getProductsCollection($storeId)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->productFactory->create();
        $todayStartOfDayDate = $this->localeDate->date()->setTime('00:00:00')
            ->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate = $this->localeDate->date()->setTime('23:59:59')
            ->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT);
        /** @var $products \Magento\Catalog\Model\Resource\Product\Collection */
        $products = $product->getResourceCollection();
        $products->setStoreId($storeId);
        $products->addStoreFilter()->addAttributeToFilter(
            'news_from_date',
            array(
                'or' => array(
                    0 => array('date' => true, 'to' => $todayEndOfDayDate),
                    1 => array('is' => new \Zend_Db_Expr('null'))
                )
            ),
            'left'
        )->addAttributeToFilter(
            'news_to_date',
            array(
                'or' => array(
                    0 => array('date' => true, 'from' => $todayStartOfDayDate),
                    1 => array('is' => new \Zend_Db_Expr('null'))
                )
            ),
            'left'
        )->addAttributeToFilter(array(
            array('attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')),
            array('attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null'))
        ))->addAttributeToSort('news_from_date', 'desc')
        ->addAttributeToSelect(array('name', 'short_description', 'description'), 'inner')
        ->addAttributeToSelect(
            array(
                'price',
                'special_price',
                'special_from_date',
                'special_to_date',
                'msrp_enabled',
                'msrp_display_actual_price_type',
                'msrp',
                'thumbnail'
            ),
            'left'
        )->applyFrontendPriceLimitations();
        $products->setVisibility($this->visibility->getVisibleInCatalogIds());

        return $products;
    }
}
