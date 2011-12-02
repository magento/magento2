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
 * Catalog Custom Category design Model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Design extends Mage_Core_Model_Abstract
{
    const APPLY_FOR_PRODUCT     = 1;
    const APPLY_FOR_CATEGORY    = 2;

    /**
     * Apply custom design
     *
     * @param string $design
     */
    public function applyCustomDesign($design)
    {
        Mage::getDesign()->setDesignTheme($design);
        return $this;
    }

    /**
     * Get custom layout settings
     *
     * @param Mage_Catalog_Model_Category|Mage_Catalog_Model_Product $object
     * @return Varien_Object
     */
    public function getDesignSettings($object)
    {
        if ($object instanceof Mage_Catalog_Model_Product) {
            $currentCategory = $object->getCategory();
        } else {
            $currentCategory = $object;
        }

        $category = null;
        if ($currentCategory) {
            $category = $currentCategory->getParentDesignCategory($currentCategory);
        }

        if ($object instanceof Mage_Catalog_Model_Product) {
            if ($category && $category->getCustomApplyToProducts()) {
                return $this->_mergeSettings($this->_extractSettings($category), $this->_extractSettings($object));
            } else {
                return $this->_extractSettings($object);
            }
        } else {
             return $this->_extractSettings($category);
        }
    }

    /**
     * Extract custom layout settings from category or product object
     *
     * @param Mage_Catalog_Model_Category|Mage_Catalog_Model_Product $object
     * @return Varien_Object
     */
    protected function _extractSettings($object)
    {
        $settings = new Varien_Object;
        if (!$object) {
            return $settings;
        }
        $date = $object->getCustomDesignDate();
        if (array_key_exists('from', $date) && array_key_exists('to', $date)
            && Mage::app()->getLocale()->isStoreDateInInterval(null, $date['from'], $date['to'])) {
                $settings->setCustomDesign($object->getCustomDesign())
                    ->setPageLayout($object->getPageLayout())
                    ->setLayoutUpdates((array)$object->getCustomLayoutUpdate());
        }
        return $settings;
    }

    /**
     * Merge custom design settings
     *
     * @param Varien_Object $categorySettings
     * @param Varien_Object $productSettings
     * @return Varien_Object
     */
    protected function _mergeSettings($categorySettings, $productSettings)
    {
        if ($productSettings->getCustomDesign()) {
            $categorySettings->setCustomDesign($productSettings->getCustomDesign());
        }
        if ($productSettings->getPageLayout()) {
            $categorySettings->setPageLayout($productSettings->getPageLayout());
        }
        if ($productSettings->getLayoutUpdates()) {
            $update = array_merge($categorySettings->getLayoutUpdates(), $productSettings->getLayoutUpdates());
            $categorySettings->setLayoutUpdates($update);
        }
        return $categorySettings;
    }
}
