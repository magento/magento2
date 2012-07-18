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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend menu block
 *
 * @method Mage_Backend_Block_Menu setAdditionalCacheKeyInfo(array $cacheKeyInfo)
 * @method array getAdditionalCacheKeyInfo()
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Block_Menu extends Mage_Backend_Block_Template
{
    const CACHE_TAGS = 'BACKEND_MAINMENU';


    /**
     * @var string
     */
    protected $_containerRendererBlock;

    /**
     * @var string
     */
    protected $_itemRendererBlock;

    /**
     * Backend URL instance
     *
     * @var Mage_Backend_Model_Url
     */
    protected $_url;

    /**
     * Current selected item
     *
     * @var Mage_Backend_Model_Menu_Item|null|bool
     */
    protected $_activeItemModel = null;

    /**
     * Initialize template and cache settings
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_url = Mage::getModel('Mage_Backend_Model_Url');
        $this->setCacheTags(array(self::CACHE_TAGS));
    }

    /**
     * Retrieve cache lifetime
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        return 86400;
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = array(
            'admin_top_nav',
            $this->getActive(),
            Mage::getSingleton('Mage_Backend_Model_Auth_Session')->getUser()->getId(),
            Mage::app()->getLocale()->getLocaleCode()
        );
        // Add additional key parameters if needed
        $additionalCacheKeyInfo = $this->getAdditionalCacheKeyInfo();
        if (is_array($additionalCacheKeyInfo) && !empty($additionalCacheKeyInfo)) {
            $cacheKeyInfo = array_merge($cacheKeyInfo, $additionalCacheKeyInfo);
        }
        return $cacheKeyInfo;
    }

    /**
     * Processing block html after rendering
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        $html = preg_replace_callback(
            '#'.Mage_Backend_Model_Url::SECRET_KEY_PARAM_NAME.'/\$([^\/].*)/([^\$].*)\$#U',
            array($this, '_callbackSecretKey'),
            $html
        );

        return $html;
    }

    /**
     * Replace Callback Secret Key
     *
     * @param array $match
     * @return string
     */
    protected function _callbackSecretKey($match)
    {
        return Mage_Backend_Model_Url::SECRET_KEY_PARAM_NAME . '/'
            . $this->_url->getSecretKey($match[1], $match[2]);
    }

    /**
     * Get menu config model
     * @return Mage_Backend_Model_Menu
     */
    public function getMenuModel()
    {
        return Mage::getSingleton('Mage_Backend_Model_Menu_Config')->getMenu();
    }

    /**
     * Render menu container
     * @param $menu
     * @param $level
     * @return string HTML
     */
    public function renderMenuContainer($menu, $level = 0)
    {
        $block = $this->getChildBlock($this->getContainerRendererBlock());
        $block->setMenu($menu);
        $block->setLevel($level);
        $block->setMenuBlock($this);
        return $block->toHtml();
    }

    /**
     * Set container renderer block name
     * @param string $renderer
     * @return Mage_Backend_Block_Menu
     */
    public function setContainerRendererBlock($renderer)
    {
        $this->_containerRendererBlock = $renderer;
        return $this;
    }

    /**
     * Get container renderer block name
     * @return string
     */
    public function getContainerRendererBlock()
    {
        return $this->_containerRendererBlock;
    }

    /**
     * Set item renderer block name
     * @param string $renderer
     * @return Mage_Backend_Block_Menu
     */
    public function setItemRendererBlock($renderer)
    {
        $this->_itemRendererBlock = $renderer;
        return $this;
    }

    /**
     * Get item renderer block name
     * @return string
     */
    public function getItemRendererBlock()
    {
        return $this->_itemRendererBlock;
    }

    /**
     * Get current selected menu item
     *
     * @return Mage_Backend_Model_Menu_Item|null|bool
     */
    public function getActiveItemModel()
    {
        if (is_null($this->_activeItemModel)) {
            $this->_activeItemModel = $this->getMenuModel()->get($this->getActive());
            if (false == ($this->_activeItemModel instanceof Mage_Backend_Model_Menu_Item)) {
                $this->_activeItemModel = false;
            }
        }
        return $this->_activeItemModel;
    }
}
