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
 * Class Special
 * @package Magento\Catalog\Model\Rss\Product
 */
class Special
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
    }


    /**
     * @param int $storeId
     * @param int $customerGroupId
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    public function getProductsCollection($storeId, $customerGroupId)
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->productFactory->create();
        $product->setStoreId($storeId);

        $collection = $product->getResourceCollection()
            ->addPriceDataFieldFilter('%s < %s', array('final_price', 'price'))
            ->addPriceData($customerGroupId, $websiteId)
            ->addAttributeToSelect(
                array(
                    'name',
                    'short_description',
                    'description',
                    'price',
                    'thumbnail',
                    'special_price',
                    'special_to_date',
                    'msrp_enabled',
                    'msrp_display_actual_price_type',
                    'msrp'
                ),
                'left'
            )->addAttributeToSort('name', 'asc');

        return $collection;
    }
}
