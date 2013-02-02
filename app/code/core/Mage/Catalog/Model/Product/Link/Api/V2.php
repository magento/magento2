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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product link api V2
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Link_Api_V2 extends Mage_Catalog_Model_Product_Link_Api
{
    /**
     * Add product link association
     *
     * @param string $type
     * @param int|string $productId
     * @param int|string $linkedProductId
     * @param array $data
     * @return boolean
     */
    public function assign($type, $productId, $linkedProductId, $data = array(), $identifierType = null)
    {
        $typeId = $this->_getTypeId($type);

        $product = $this->_initProduct($productId, $identifierType);

        $link = $product->getLinkInstance()
            ->setLinkTypeId($typeId);

        $collection = $this->_initCollection($link, $product);
        $idBySku = $product->getIdBySku($linkedProductId);
        if ($idBySku) {
            $linkedProductId = $idBySku;
        }

        $links = $this->_collectionToEditableArray($collection);

        $links[(int)$linkedProductId] = array();
        foreach ($collection->getLinkModel()->getAttributes() as $attribute) {
            if (isset($data->$attribute['code'])) {
                $links[(int)$linkedProductId][$attribute['code']] = $data->$attribute['code'];
            }
        }

        try {
            $link->getResource()->saveProductLinks($product, $links, $typeId);
        } catch (Exception $e) {
            $this->_fault('data_invalid', Mage::helper('Mage_Catalog_Helper_Data')->__('Link product does not exist.'));
        }

        return true;
    }

    /**
     * Update product link association info
     *
     * @param string $type
     * @param int|string $productId
     * @param int|string $linkedProductId
     * @param array $data
     * @return boolean
     */
    public function update($type, $productId, $linkedProductId, $data = array(), $identifierType = null)
    {
        $typeId = $this->_getTypeId($type);

        $product = $this->_initProduct($productId, $identifierType);

        $link = $product->getLinkInstance()
            ->setLinkTypeId($typeId);

        $collection = $this->_initCollection($link, $product);

        $links = $this->_collectionToEditableArray($collection);

        $idBySku = $product->getIdBySku($linkedProductId);
        if ($idBySku) {
            $linkedProductId = $idBySku;
        }

        foreach ($collection->getLinkModel()->getAttributes() as $attribute) {
            if (isset($data->$attribute['code'])) {
                $links[(int)$linkedProductId][$attribute['code']] = $data->$attribute['code'];
            }
        }

        try {
            $link->getResource()->saveProductLinks($product, $links, $typeId);
        } catch (Exception $e) {
            $this->_fault('data_invalid', Mage::helper('Mage_Catalog_Helper_Data')->__('Link product does not exist.'));
        }

        return true;
    }
}
