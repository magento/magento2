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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Home categories list renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Home extends Mage_XmlConnect_Block_Catalog
{
    /**
     * Category list limitation
     */
    const HOME_PAGE_CATEGORIES_COUNT = 6;

    /**
     * Render home category list xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $homeXmlObj Mage_XmlConnect_Model_Simplexml_Element */
        $homeXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', array('data' => '<home></home>'));

        $categoryCollection = array();
        $helper = Mage::helper('Mage_Catalog_Helper_Category');
        $categoryCount = 0;
        foreach ($helper->getStoreCategories() as $child) {
            if ($child->getIsActive()) {
                $categoryCollection[] = $child;
                $categoryCount++;
            }
            if ($categoryCount == self::HOME_PAGE_CATEGORIES_COUNT) {
                break;
            }
        }

        if (sizeof($categoryCollection)) {
            $itemsXmlObj = $homeXmlObj->addChild('categories');
        }

        foreach ($categoryCollection as $item) {
            /** @var $item Mage_Catalog_Model_Category */
            $item = Mage::getModel('Mage_Catalog_Model_Category')->load($item->getId());
            $itemXmlObj = $itemsXmlObj->addChild('item');
            $itemXmlObj->addChild('label', $homeXmlObj->escapeXml($item->getName()));
            $itemXmlObj->addChild('entity_id', $item->getId());
            $itemXmlObj->addChild('content_type', $item->hasChildren() ? 'categories' : 'products');
            $icon = Mage::helper('Mage_XmlConnect_Helper_Catalog_Category_Image')->initialize($item, 'thumbnail')
                ->resize(Mage::helper('Mage_XmlConnect_Helper_Image')->getImageSizeForContent('category'));

            $iconXml = $itemXmlObj->addChild('icon', $icon);
            $file = Mage::helper('Mage_XmlConnect_Helper_Data')->urlToPath($icon);
            $iconXml->addAttribute('modification_time', filemtime($file));
        }
        $homeXmlObj->addChild('home_banner', '/current/media/catalog/category/banner_home.png');

        return $homeXmlObj->asNiceXml();
    }
}
