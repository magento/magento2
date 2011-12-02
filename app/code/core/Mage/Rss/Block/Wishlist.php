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
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer Shared Wishlist Rss Block
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rss_Block_Wishlist extends Mage_Wishlist_Block_Abstract
{
    /**
     * Customer instance
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp_rss';

    /**
     * Retrieve Wishlist model
     *
     * @return Mage_Wishlist_Model_Wishlist
     */
    protected function _getWishlist()
    {
        if (is_null($this->_wishlist)) {
            $this->_wishlist = Mage::getModel('Mage_Wishlist_Model_Wishlist');
            if ($this->_getCustomer()->getId()) {
                $this->_wishlist->loadByCustomer($this->_getCustomer());
            }
        }
        return $this->_wishlist;
    }

    /**
     * Retrieve Customer instance
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        if (is_null($this->_customer)) {
            $this->_customer = Mage::getModel('Mage_Customer_Model_Customer');

            $params = Mage::helper('Mage_Core_Helper_Data')->urlDecode($this->getRequest()->getParam('data'));
            $data   = explode(',', $params);
            $cId    = abs(intval($data[0]));
            if ($cId && ($cId == Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId()) ) {
                $this->_customer->load($cId);
            }
        }

        return $this->_customer;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        /* @var $rssObj Mage_Rss_Model_Rss */
        $rssObj = Mage::getModel('Mage_Rss_Model_Rss');

        if ($this->_getWishlist()->getId()) {
            $newUrl = Mage::getUrl('wishlist/shared/index', array(
                'code'  => $this->_getWishlist()->getSharingCode()
            ));

            $title  = Mage::helper('Mage_Rss_Helper_Data')->__('%s\'s Wishlist', $this->_getCustomer()->getName());
            $lang   = Mage::getStoreConfig('general/locale/code');

            $rssObj->_addHeader(array(
                'title'         => $title,
                'description'   => $title,
                'link'          => $newUrl,
                'charset'       => 'UTF-8',
                'language'      => $lang
            ));

            /** @var $wishlistItem Mage_Wishlist_Model_Item*/
            foreach ($this->getWishlistItems() as $wishlistItem) {
                /* @var $product Mage_Catalog_Model_Product */
                $product = $wishlistItem->getProduct();
                $productUrl = $this->getProductUrl($product);
                $product->setAllowedInRss(true);
                $product->setAllowedPriceInRss(true);
                $product->setProductUrl($productUrl);
                $args = array('product' => $product);

                Mage::dispatchEvent('rss_wishlist_xml_callback', $args);

                if (!$product->getAllowedInRss()) {
                    continue;
                }

                $description = '<table><tr><td><a href="' . $productUrl . '"><img src="'
                    . $this->helper('Mage_Catalog_Helper_Image')->init($product, 'thumbnail')->resize(75, 75)
                    . '" border="0" align="left" height="75" width="75"></a></td>'
                    . '<td style="text-decoration:none;">'
                    . $this->helper('Mage_Catalog_Helper_Output')
                        ->productAttribute($product, $product->getShortDescription(), 'short_description')
                    . '<p>';

                if ($product->getAllowedPriceInRss()) {
                    $description .= $this->getPriceHtml($product,true);
                }
                $description .= '</p>';
                if ($this->hasDescription($product)) {
                    $description .= '<p>' . Mage::helper('Mage_Wishlist_Helper_Data')->__('Comment:')
                        . ' ' . $this->helper('Mage_Catalog_Helper_Output')
                            ->productAttribute($product, $product->getDescription(), 'description')
                        . '<p>';
                }

                $description .= '</td></tr></table>';

                $rssObj->_addEntry(array(
                    'title'         => $this->helper('Mage_Catalog_Helper_Output')
                        ->productAttribute($product, $product->getName(), 'name'),
                    'link'          => $productUrl,
                    'description'   => $description,
                ));
            }
        }
        else {
            $rssObj->_addHeader(array(
                'title'         => Mage::helper('Mage_Rss_Helper_Data')->__('Cannot retrieve the wishlist'),
                'description'   => Mage::helper('Mage_Rss_Helper_Data')->__('Cannot retrieve the wishlist'),
                'link'          => Mage::getUrl(),
                'charset'       => 'UTF-8',
            ));
        }

        return $rssObj->createRssXml();
    }

    /**
     * Retrieve Product View URL
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getProductUrl($product, $additional = array())
    {
        $additional['_rss'] = true;
        return parent::getProductUrl($product, $additional);
    }

    /**
     * Adding customized price template for product type, used as action in layouts
     *
     * @param string $type Catalog Product Type
     * @param string $block Block Type
     * @param string $template Template
     */
    public function addPriceBlockType($type, $block = '', $template = '')
    {
        if ($type) {
            $this->_priceBlockTypes[$type] = array(
                'block' => $block,
                'template' => $template
            );
        }
    }
}
