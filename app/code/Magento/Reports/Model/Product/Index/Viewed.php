<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Model\Product\Index;

/**
 * Catalog Viewed Product Index
 *
 * @method \Magento\Reports\Model\Product\Index\Viewed setVisitorId(int $value)
 * @method \Magento\Reports\Model\Product\Index\Viewed setCustomerId(int $value)
 * @method int getProductId()
 * @method \Magento\Reports\Model\Product\Index\Viewed setProductId(int $value)
 * @method \Magento\Reports\Model\Product\Index\Viewed setStoreId(int $value)
 * @method string getAddedAt()
 * @method \Magento\Reports\Model\Product\Index\Viewed setAddedAt(string $value)
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @api
 * @since 100.0.2
 */
class Viewed extends \Magento\Reports\Model\Product\Index\AbstractIndex
{
    /**
     * Cache key name for Count of product index
     *
     * @var string
     */
    protected $_countCacheKey = 'product_index_viewed_count';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Reports\Model\ResourceModel\Product\Index\Viewed::class);
    }

    /**
     * Retrieve Exclude Product Ids List for Collection
     *
     * @return array
     */
    public function getExcludeProductIds()
    {
        $productIds = [];

        if ($this->_registry->registry('current_product')) {
            $productIds[] = $this->_registry->registry('current_product')->getId();
        }

        return $productIds;
    }
}
