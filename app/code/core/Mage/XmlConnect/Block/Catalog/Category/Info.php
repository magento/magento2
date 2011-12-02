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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Category info xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Catalog_Category_Info extends Mage_XmlConnect_Block_Catalog
{
    /**
     * Produce category info xml object
     *
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function getCategoryInfoXmlObject()
    {
        $infoXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<category_info></category_info>');
        $category   = $this->getCategory();
        if (is_object($category) && $category->getId()) {
            /**
             * @var string $title
             *
             * Copied data from "getDefaultApplicationDesignTabs()" method in "Mage_XmlConnect_Helper_Data"
             */
            $title = $this->__('Shop');
            if ($category->getParentCategory()->getLevel() > 1) {
                $title = $infoXmlObj->xmlentities($category->getParentCategory()->getName());
            }

            $infoXmlObj->addChild('parent_title', $title);
            $pId = 0;
            if ($category->getLevel() > 1) {
                $pId = $category->getParentId();
            }
            $infoXmlObj->addChild('parent_id', $pId);
        }

        return $infoXmlObj;
    }

    /**
     * Render category info xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getCategoryInfoXmlObject()->asNiceXml();
    }
}
