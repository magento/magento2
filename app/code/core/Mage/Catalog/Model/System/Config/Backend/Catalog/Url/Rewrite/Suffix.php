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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Url rewrite suffix backend
 */
class Mage_Catalog_Model_System_Config_Backend_Catalog_Url_Rewrite_Suffix extends Mage_Core_Model_Config_Data
{
    /**
     * Check url rewrite suffix - whether we can support it
     *
     * @return Mage_Catalog_Model_System_Config_Backend_Catalog_Url_Rewrite_Suffix
     */
    protected function _beforeSave()
    {
        Mage::helper('Mage_Core_Helper_Url_Rewrite')->validateSuffix($this->getValue());
        return $this;
    }
}
