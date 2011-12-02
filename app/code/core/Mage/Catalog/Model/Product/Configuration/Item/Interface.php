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
 * Product configurational item interface
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
interface Mage_Catalog_Model_Product_Configuration_Item_Interface
{
    /**
     * Retrieve associated product
     *
     * @return Mage_Catalog_Model_Product
     */
    function getProduct();

    /**
     * Get item option by code
     *
     * @param   string $code
     * @return  Mage_Catalog_Model_Product_Configuration_Item_Option_Interface
     */
    public function getOptionByCode($code);

    /**
     * Returns special download params (if needed) for custom option with type = 'file''
     * Return null, if not special params needed'
     * Or return Varien_Object with any of the following indexes:
     *  - 'url' - url of controller to give the file
     *  - 'urlParams' - additional parameters for url (custom option id, or item id, for example)
     *
     * @return null|Varien_Object
     */
    public function getFileDownloadParams();
}
