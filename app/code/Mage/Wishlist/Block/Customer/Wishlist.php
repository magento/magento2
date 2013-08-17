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
 * Wishlist block customer items
 *
 * @category   Mage
 * @package    Mage_Wishlist
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Wishlist_Block_Customer_Wishlist extends Mage_Wishlist_Block_Abstract
{
    /*
     * List of product options rendering configurations by product type
     */
    protected $_optionsCfg = array();

    /**
     * Add wishlist conditions to collection
     *
     * @param  Mage_Wishlist_Model_Resource_Item_Collection $collection
     * @return Mage_Wishlist_Block_Customer_Wishlist
     */
    protected function _prepareCollection($collection)
    {
        $collection->setInStockFilter(true)->setOrder('added_at', 'ASC');
        return $this;
    }

    /**
     * Preparing global layout
     *
     * @return Mage_Wishlist_Block_Customer_Wishlist
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->__('My Wish List'));
        }
    }

    /**
     * Retrieve Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Sets all options render configurations
     *
     * @deprecated after 1.6.2.0
     * @param null|array $optionCfg
     * @return Mage_Wishlist_Block_Customer_Wishlist
     */
    public function setOptionsRenderCfgs($optionCfg)
    {
        $this->_optionsCfg = $optionCfg;
        return $this;
    }

    /**
     * Returns all options render configurations
     *
     * @deprecated after 1.6.2.0
     * @return array
     */
    public function getOptionsRenderCfgs()
    {
        return $this->_optionsCfg;
    }

    /*
     * Adds config for rendering product type options
     *
     * @deprecated after 1.6.2.0
     * @param string $productType
     * @param string $helperName
     * @param null|string $template
     * @return Mage_Wishlist_Block_Customer_Wishlist
     */
    public function addOptionsRenderCfg($productType, $helperName, $template = null)
    {
        $this->_optionsCfg[$productType] = array('helper' => $helperName, 'template' => $template);
        return $this;
    }

    /**
     * Returns html for showing item options
     *
     * @deprecated after 1.6.2.0
     * @param string $productType
     * @return array|null
     */
    public function getOptionsRenderCfg($productType)
    {
        if (isset($this->_optionsCfg[$productType])) {
            return $this->_optionsCfg[$productType];
        } elseif (isset($this->_optionsCfg['default'])) {
            return $this->_optionsCfg['default'];
        } else {
            return null;
        }
    }

    /**
     * Returns html for showing item options
     *
     * @deprecated after 1.6.2.0
     * @param Mage_Wishlist_Model_Item $item
     * @return string
     */
    public function getDetailsHtml(Mage_Wishlist_Model_Item $item)
    {
        $cfg = $this->getOptionsRenderCfg($item->getProduct()->getTypeId());
        if (!$cfg) {
            return '';
        }

        $helper = Mage::helper($cfg['helper']);
        if (!($helper instanceof Mage_Catalog_Helper_Product_Configuration_Interface)) {
            Mage::throwException($this->__("Helper for wish list options rendering doesn't implement required interface."));
        }

        $block = $this->getChildBlock('item_options');
        if (!$block) {
            return '';
        }

        if ($cfg['template']) {
            $template = $cfg['template'];
        } else {
            $cfgDefault = $this->getOptionsRenderCfg('default');
            if (!$cfgDefault) {
                return '';
            }
            $template = $cfgDefault['template'];
        }

        return $block->setTemplate($template)
            ->setOptionList($helper->getOptions($item))
            ->toHtml();
    }

    /**
     * Returns qty to show visually to user
     *
     * @deprecated after 1.6.2.0
     * @param Mage_Wishlist_Model_Item $item
     * @return float
     */
    public function getAddToCartQty(Mage_Wishlist_Model_Item $item)
    {
        $qty = $this->getQty($item);
        return $qty ? $qty : 1;
    }
}
