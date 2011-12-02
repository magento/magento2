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
 * Catalog flat helper
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Helper_Category_Flat extends Mage_Core_Helper_Abstract
{
    const XML_PATH_IS_ENABLED_FLAT_CATALOG_CATEGORY = 'catalog/frontend/flat_catalog_category';

    /**
     * Return true if flat catalog is enabled, rebuileded and is not Admin
     *
     * @param boolean $skipAdmin
     * @return boolean
     */
    public function isEnabled($skipAdminCheck = false)
    {
        $flatFlag = Mage::getStoreConfigFlag(self::XML_PATH_IS_ENABLED_FLAT_CATALOG_CATEGORY);
        $isFront = !Mage::app()->getStore()->isAdmin();
        if ($skipAdminCheck === true) {
            $isFront = true;
        }

        return (boolean) $flatFlag && $isFront;
    }

    /**
     * Return true if catalog category flat data rebuilt
     *
     * @return boolean
     */
    public function isRebuilt()
    {
        return Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Category_Flat')->isRebuilt();
    }

    /**
     * Back Flat compatibility: check is built and enabled flat
     *
     * @return bool
     */
    public function isBuilt()
    {
        return $this->isEnabled(true);
    }
}
