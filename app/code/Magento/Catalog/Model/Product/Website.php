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


/**
 * Catalog Product Website Model
 *
 * @method \Magento\Catalog\Model\Resource\Product\Website _getResource()
 * @method \Magento\Catalog\Model\Resource\Product\Website getResource()
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
        $this->_init('Magento\Catalog\Model\Resource\Product\Website');
    }

    /**
     * Retrieve Resource instance wrapper
     *
     * @return \Magento\Catalog\Model\Resource\Product\Website
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function removeProducts($websiteIds, $productIds)
    {
        try {
            $this->_getResource()->removeProducts($websiteIds, $productIds);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Model\Exception(__('Something went wrong removing products from the websites.'));
        }
        return $this;
    }

    /**
     * Add products to websites
     *
     * @param array $websiteIds
     * @param array $productIds
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function addProducts($websiteIds, $productIds)
    {
        try {
            $this->_getResource()->addProducts($websiteIds, $productIds);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Model\Exception(__('Something went wrong adding products to websites.'));
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
