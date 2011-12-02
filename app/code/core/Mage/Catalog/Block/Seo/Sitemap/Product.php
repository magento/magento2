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
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * SEO Products Sitemap block
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Seo_Sitemap_Product extends Mage_Catalog_Block_Seo_Sitemap_Abstract
{

    /**
     * Initialize products collection
     *
     * @return Mage_Catalog_Block_Seo_Sitemap_Category
     */
    protected function _prepareLayout()
    {
        $collection = Mage::getModel('Mage_Catalog_Model_Product')->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addStoreFilter()
            ->setVisibility(Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getVisibleInCatalogIds());

        $this->setCollection($collection);

        return $this;
    }

    /**
     * Get item URL
     *
     * @param Mage_Catalog_Model_Product $category
     * @return string
     */
    public function getItemUrl($product)
    {
        $helper = Mage::helper('Mage_Catalog_Helper_Product');
        /* @var $helper Mage_Catalog_Helper_Product */
        return $helper->getProductUrl($product);
    }

}
