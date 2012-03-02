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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * SEO sitemap controller
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Seo_SitemapController extends Mage_Core_Controller_Front_Action
{

    /**
     * Check if SEO sitemap is enabled in configuration
     *
     * @return Mage_Catalog_Seo_SitemapController
     */
    public function preDispatch(){
        parent::preDispatch();
        if(!Mage::getStoreConfig('catalog/seo/site_map')){
              $this->_redirect('noroute');
              $this->setFlag('',self::FLAG_NO_DISPATCH,true);
        }
        return $this;
    }

    /**
     * Display categories listing
     *
     */
    public function categoryAction()
    {
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();
        if (Mage::helper('Mage_Catalog_Helper_Map')->getIsUseCategoryTreeMode()) {
            $update->addHandle(strtolower($this->getFullActionName()).'_tree');
        }
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->renderLayout();
    }

    /**
     * Display products listing
     *
     */
    public function productAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

}
