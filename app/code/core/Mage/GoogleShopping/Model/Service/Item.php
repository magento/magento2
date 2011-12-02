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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Content Item Model
 *
 * @category   Mage
 * @package    Mage_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleShopping_Model_Service_Item extends Mage_GoogleShopping_Model_Service
{
    /**
     * Return Store level Service Instance
     *
     * @param int $storeId
     * @return Varien_Gdata_Gshopping_Content
     */
    public function getService($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->getStoreId();
        }
        return parent::getService($storeId);
    }

    /**
     * Insert Item into Google Content
     *
     * @param Mage_GoogleShopping_Model_Item $item
     * @return Mage_GoogleShopping_Model_Service_Item
     */
    public function insert($item)
    {
        $service = $this->getService();
        $entry = $service->newEntry();
        $item->getType()
            ->convertProductToEntry($item->getProduct(), $entry);

        $entry = $service->insertItem($entry);
        $published = $this->convertContentDateToTimestamp($entry->getPublished()->getText());

        $item->setGcontentItemId($entry->getId())
            ->setPublished($published);

        $expires = $this->_getAttributeValue($entry, 'expiration_date');
        if ($expires) {
            $expires = $this->convertContentDateToTimestamp($expires);
            $item->setExpires($expires);
        }
        return $this;
    }

    /**
     * Update Item data in Google Content
     *
     * @param Mage_GoogleShopping_Model_Item $item
     * @return Mage_GoogleShopping_Model_Service_Item
     */
    public function update($item)
    {
        $service = $this->getService();
        $query = $this->_buildItemQuery($item);
        $entry = $service->getItem($query);

        $stats = $this->_getEntryStats($entry);
        if (isset($stats['expires'])) {
            $item->setExpires($stats['expires']);
        }
        $entry = $item->getType()->convertProductToEntry($item->getProduct(), $entry);
        $entry = $service->updateItem($entry);

        return $this;
    }

    /**
     * Delete Item from Google Content
     *
     * @param Mage_GoogleShopping_Model_Item $item
     * @return Mage_GoogleShopping_Model_Service_Item
     */
    public function delete($item)
    {
        $service = $this->getService();
        $query = $this->_buildItemQuery($item);
        $service->delete($query->getQueryUrl());

        return $this;
    }

    /**
     * Convert Google Content date format to unix timestamp
     * Ex. 2008-12-08T16:57:23Z -> 2008-12-08 16:57:23
     *
     * @param string Google Content datetime
     * @return int
     */
    public function convertContentDateToTimestamp($gContentDate)
    {
        return Mage::getSingleton('Mage_Core_Model_Date')->date(null, $gContentDate);
    }

    /**
     * Return Google Content Item Attribute Value
     *
     * @param Varien_Gdata_Gshopping_Entry $entry
     * @param string $name Google Content attribute name
     * @return string|null Attribute value
     */
    protected function _getAttributeValue($entry, $name)
    {
        $attribute = $entry->getContentAttributeByName($name);
        return ($attribute instanceof Varien_Gdata_Gshopping_Extension_Attribute)
            ? $attribute->text
            : null;
    }

    /**
     * Retrieve item query for Google Content
     *
     * @param Mage_GoogleShopping_Model_Item $item
     * @return Varien_Gdata_Gshopping_ItemQuery
     */
    protected function _buildItemQuery($item)
    {
        $storeId = $item->getStoreId();
        $service = $this->getService($storeId);

        $countryInfo = $this->getConfig()->getTargetCountryInfo($storeId);
        $itemId = Mage::helper('Mage_GoogleShopping_Helper_Data')->buildContentProductId($item->getProductId(), $item->getStoreId());

        $query = $service->newItemQuery()
            ->setId($itemId)
            ->setTargetCountry($this->getConfig()->getTargetCountry($storeId))
            ->setLanguage($countryInfo['language']);

        return $query;
    }

    /**
     * Return item stats array based on Zend Gdata Entry object
     *
     * @param Varien_Gdata_Gshopping_Entry $entry
     * @return array
     */
    protected function _getEntryStats($entry)
    {
        $result = array();
        $expirationDate = $entry->getContentAttributeByName('expiration_date');
        if ($expirationDate instanceof Varien_Gdata_Gshopping_Extension_Attribute) {
            $result['expires'] = $this->convertContentDateToTimestamp($expirationDate->text);
        }

        return $result;
    }
}
