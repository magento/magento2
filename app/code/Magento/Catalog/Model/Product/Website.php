<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Catalog Product Website Model
 *
 * @method \Magento\Catalog\Model\ResourceModel\Product\Website _getResource()
 * @method \Magento\Catalog\Model\ResourceModel\Product\Website getResource()
 * @method int getWebsiteId()
 * @method \Magento\Catalog\Model\Product\Website setWebsiteId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product;

class Website extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\ResourceModel\Product\Website');
    }

    /**
     * Retrieve Resource instance wrapper
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Website
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Removes products from websites
     *
     * @param array $websiteIds
     * @param array $productIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeProducts($websiteIds, $productIds)
    {
        try {
            $this->_getResource()->removeProducts($websiteIds, $productIds);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while removing products from the websites.')
            );
        }
        return $this;
    }

    /**
     * Add products to websites
     *
     * @param array $websiteIds
     * @param array $productIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProducts($websiteIds, $productIds)
    {
        try {
            $this->_getResource()->addProducts($websiteIds, $productIds);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while adding products to websites.')
            );
        }
        return $this;
    }

    /**
     * Retrieve product websites
     * Return array with key as product ID and value array of websites
     *
     * @param int|array $productIds
     * @return array
     */
    public function getWebsites($productIds)
    {
        return $this->_getResource()->getWebsites($productIds);
    }
}
