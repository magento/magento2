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
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Downloadable links API model
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_Model_Link_Api_V2 extends Mage_Downloadable_Model_Link_Api
{
    /**
     * Clean the object, leave only property values
     *
     * @param object $var
     * @return void
     */
    protected function _prepareData(&$var)
    {
        if (is_object($var)) {
            $var = get_object_vars($var);
            foreach ($var as $key => &$value) {
                $this->_prepareData($value);
            }
        }
    }

    /**
     * Add downloadable content to product
     *
     * @param int|string $productId
     * @param object $resource
     * @param string $resourceType
     * @param string|int $store
     * @param string $identifierType ('sku'|'id')
     * @return type
     */
    public function add($productId, $resource, $resourceType, $store = null, $identifierType = null)
    {
        $this->_prepareData($resource);
        return parent::add($productId, $resource, $resourceType, $store, $identifierType);
    }
}
