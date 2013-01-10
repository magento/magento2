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
 * @package     Mage_Wishlist
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist Product Items abstract Block
 *
 * @category   Mage
 * @package    Mage_Wishlist
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Wishlist_Block_Abstract extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Wishlist Product Items Collection
     *
     * @var Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected $_collection;

    /**
     * Store wishlist Model
     *
     * @var Mage_Wishlist_Model_Wishlist
     */
    protected $_wishlist;

    /**
     * List of block settings to render prices for different product types
     *
     * @var array
     */
    protected $_itemPriceBlockTypes = array();

    /**
     * List of block instances to render prices for different product types
     *
     * @var array
     */
    protected $_cachedItemPriceBlocks = array();

    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addItemPriceBlockType(
            'default',
            'Mage_Wishlist_Block_Render_Item_Price',
            'render/item/price.phtml'
        );
    }

    /**
     * Retrieve Wishlist Data Helper
     *
     * @return Mage_Wishlist_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('Mage_Wishlist_Helper_Data');
    }

    /**
     * Retrieve Customer Session instance
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('Mage_Customer_Model_Session');
    }

    /**
     * Retrieve Wishlist model
     *
     * @return Mage_Wishlist_Model_Wishlist
     */
    protected function _getWishlist()
    {
        return $this->_getHelper()->getWishlist();
    }

    /**
     * Prepare additional conditions to collection
     *
     * @param Mage_Wishlist_Model_Resource_Item_Collection $collection
     * @return Mage_Wishlist_Block_Customer_Wishlist
     */
    protected function _prepareCollection($collection)
    {
        return $this;
    }

    /**
     * Create wishlist item collection
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected function _createWishlistItemCollection()
    {
        return $this->_getWishlist()->getItemCollection();
    }

    /**
     * Retrieve Wishlist Product Items collection
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function getWishlistItems()
    {
        if (is_null($this->_collection)) {
            $this->_collection = $this->_createWishlistItemCollection();
            $this->_prepareCollection($this->_collection);
        }

        return $this->_collection;
    }

    /**
     * Retrieve wishlist instance
     *
     * @return Mage_Wishlist_Model_Wishlist
     */
    public function getWishlistInstance()
    {
        return $this->_getWishlist();
    }

    /**
     * Retrieve URL for Removing item from wishlist
     *
     * @param Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     *
     * @return string
     */
    public function getItemRemoveUrl($item)
    {
        return $this->_getHelper()->getRemoveUrl($item);
    }

    /**
     * Retrieve Add Item to shopping cart URL
     *
     * @param string|Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     * @return string
     */
    public function getItemAddToCartUrl($item)
    {
        return $this->_getHelper()->getAddToCartUrl($item);
    }

    /**
     * Retrieve Add Item to shopping cart URL from shared wishlist
     *
     * @param string|Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     * @return string
     */
    public function getSharedItemAddToCartUrl($item)
    {
        return $this->_getHelper()->getSharedAddToCartUrl($item);
    }

    /**
     * Retrieve URL for adding Product to wishlist
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getAddToWishlistUrl($product)
    {
        return $this->_getHelper()->getAddUrl($product);
    }

     /**
     * Returns item configure url in wishlist
     *
     * @param Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $product
     *
     * @return string
     */
    public function getItemConfigureUrl($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $id = $product->getWishlistItemId();
        } else {
            $id = $product->getId();
        }
        $params = array('id' => $id);

        return $this->getUrl('wishlist/index/configure/', $params);
    }


    /**
     * Retrieve Escaped Description for Wishlist Item
     *
     * @param Mage_Catalog_Model_Product $item
     * @return string
     */
    public function getEscapedDescription($item)
    {
        if ($item->getDescription()) {
            return $this->escapeHtml($item->getDescription());
        }
        return '&nbsp;';
    }

    /**
     * Check Wishlist item has description
     *
     * @param Mage_Catalog_Model_Product $item
     * @return bool
     */
    public function hasDescription($item)
    {
        return trim($item->getDescription()) != '';
    }

    /**
     * Retrieve formated Date
     *
     * @param string $date
     * @return string
     */
    public function getFormatedDate($date)
    {
        return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
    }

    /**
     * Check is the wishlist has a salable product(s)
     *
     * @return bool
     */
    public function isSaleable()
    {
        foreach ($this->getWishlistItems() as $item) {
            if ($item->getProduct()->isSaleable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve wishlist loaded items count
     *
     * @return int
     */
    public function getWishlistItemsCount()
    {
        return $this->_getWishlist()->getItemsCount();
    }

    /**
     * Retrieve Qty from item
     *
     * @param Mage_Wishlist_Model_Item|Mage_Catalog_Model_Product $item
     * @return float
     */
    public function getQty($item)
    {
        $qty = $item->getQty() * 1;
        if (!$qty) {
            $qty = 1;
        }
        return $qty;
    }

    /**
     * Check is the wishlist has items
     *
     * @return bool
     */
    public function hasWishlistItems()
    {
        return $this->getWishlistItemsCount() > 0;
    }

    /**
     * Adds special block to render price for item with specific product type
     *
     * @param string $type
     * @param string $block
     * @param string $template
     */
    public function addItemPriceBlockType($type, $block = '', $template = '')
    {
        if ($type) {
            $this->_itemPriceBlockTypes[$type] = array(
                'block' => $block,
                'template' => $template
            );
        }
    }

    /**
     * Returns block to render item with some product type
     *
     * @param string $productType
     * @return Mage_Core_Block_Template
     */
    protected function _getItemPriceBlock($productType)
    {
        if (!isset($this->_itemPriceBlockTypes[$productType])) {
            $productType = 'default';
        }

        if (!isset($this->_cachedItemPriceBlocks[$productType])) {
            $blockType = $this->_itemPriceBlockTypes[$productType]['block'];
            $template = $this->_itemPriceBlockTypes[$productType]['template'];
            $block = $this->getLayout()->createBlock($blockType)
                ->setTemplate($template);
            $this->_cachedItemPriceBlocks[$productType] = $block;
        }
        return $this->_cachedItemPriceBlocks[$productType];
    }

    /**
     * Returns product price block html
     * Overwrites parent price html return to be ready to show configured, partially configured and
     * non-configured products
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $displayMinimalPrice
     * @param string $idSuffix
     *
     * @return string
     */
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
    {
        $type_id = $product->getTypeId();
        if (Mage::helper('Mage_Catalog_Helper_Data')->canApplyMsrp($product)) {
            $realPriceHtml = $this->_preparePriceRenderer($type_id)
                ->setProduct($product)
                ->setDisplayMinimalPrice($displayMinimalPrice)
                ->setIdSuffix($idSuffix)
                ->setIsEmulateMode(true)
                ->toHtml();
            $product->setAddToCartUrl($this->getAddToCartUrl($product));
            $product->setRealPriceHtml($realPriceHtml);
            $type_id = $this->_mapRenderer;
        }

        return $this->_preparePriceRenderer($type_id)
            ->setProduct($product)
            ->setDisplayMinimalPrice($displayMinimalPrice)
            ->setIdSuffix($idSuffix)
            ->toHtml();
    }

    /**
     * Retrieve URL to item Product
     *
     * @param  Mage_Wishlist_Model_Item|Mage_Catalog_Model_Product $item
     * @param  array $additional
     * @return string
     */
    public function getProductUrl($item, $additional = array())
    {
        if ($item instanceof Mage_Catalog_Model_Product) {
            $product = $item;
        } else {
            $product = $item->getProduct();
        }
        $buyRequest = $item->getBuyRequest();
        if (is_object($buyRequest)) {
            $config = $buyRequest->getSuperProductConfig();
            if ($config && !empty($config['product_id'])) {
                $product = Mage::getModel('Mage_Catalog_Model_Product')
                    ->setStoreId(Mage::app()->getStore()->getStoreId())
                    ->load($config['product_id']);
            }
        }
        return parent::getProductUrl($product, $additional);
    }

    /**
     * Product image url getter
     *
     * @param Mage_Core_Model_Product $product
     * @return string
     */
    public function getImageUrl($product)
    {
        return (string) $this->helper('Mage_Catalog_Helper_Image')->init($product, 'small_image')->resize($this->getImageSize());
    }

    /**
     * Product image size getter
     *
     * @return int
     */
    public function getImageSize()
    {
        return $this->getVar('product_image_size', 'Mage_Wishlist');
    }
}
