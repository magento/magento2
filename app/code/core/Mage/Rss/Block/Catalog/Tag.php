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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review form block
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rss_Block_Catalog_Tag extends Mage_Rss_Block_Catalog_Abstract
{
    protected function _construct()
    {
        /*
        * setting cache to save the rss for 10 minutes
        */
        $tagModel = Mage::registry('tag_model');
        if ($tagModel) {
            $this->setCacheKey('rss_catalog_tag_' . $this->getStoreId() . '_' . $tagModel->getName());
        }
        $this->setCacheLifetime(600);
    }

    protected function _toHtml()
    {
        //store id is store view id
        $storeId = $this->_getStoreId();
        $tagModel = Mage::registry('tag_model');
        $newurl = Mage::getUrl('rss/catalog/tag/tagName/' . $tagModel->getName());
        $title = Mage::helper('Mage_Rss_Helper_Data')->__('Products tagged with %s', $tagModel->getName());
        $lang = Mage::getStoreConfig('general/locale/code');

        $rssObj = Mage::getModel('Mage_Rss_Model_Rss');
        $data = array('title' => $title,
            'description' => $title,
            'link'        => $newurl,
            'charset'     => 'UTF-8',
            'language'    => $lang
        );
        $rssObj->_addHeader($data);

        $_collection = $tagModel->getEntityCollection()
            ->addTagFilter($tagModel->getId())
            ->addStoreFilter($storeId);

        $_collection->setVisibility(Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getVisibleInCatalogIds());

        $product = Mage::getModel('Mage_Catalog_Model_Product');

        Mage::getSingleton('Mage_Core_Model_Resource_Iterator')->walk(
            Mage::getResourceHelper('Mage_Core')->getQueryUsingAnalyticFunction($_collection->getSelect()),
            array(array($this, 'addTaggedItemXml')),
            array('rssObj'=> $rssObj, 'product'=>$product),
            $_collection->getSelect()->getAdapter()
        );

        return $rssObj->createRssXml();
    }

    /**
     * Preparing data and adding to rss object
     *
     * @param array $args
     */
    public function addTaggedItemXml($args)
    {
        $product = $args['product'];

        $product->setAllowedInRss(true);
        $product->setAllowedPriceInRss(true);
        Mage::dispatchEvent('rss_catalog_tagged_item_xml_callback', $args);

        if (!$product->getAllowedInRss()) {
            //Skip adding product to RSS
            return;
        }

        $allowedPriceInRss = $product->getAllowedPriceInRss();

        $product->unsetData()->load($args['row']['entity_id']);
        $description = '<table><tr><td><a href="'.$product->getProductUrl().'">'
            . '<img src="' . $this->helper('Mage_Catalog_Helper_Image')->init($product, 'thumbnail')->resize(75, 75)
            . '" border="0" align="left" height="75" width="75"></a></td>'
            . '<td  style="text-decoration:none;">'.$product->getDescription();

        if ($allowedPriceInRss) {
            $description .= $this->getPriceHtml($product,true);
        }

        $description .='</td></tr></table>';

        $rssObj = $args['rssObj'];
        $data = array(
            'title'         => $product->getName(),
            'link'          => $product->getProductUrl(),
            'description'   => $description,
        );
        $rssObj->_addEntry($data);
    }
}
