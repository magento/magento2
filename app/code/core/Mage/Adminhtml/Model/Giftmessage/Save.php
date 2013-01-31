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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml giftmessage save model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_Giftmessage_Save extends Varien_Object
{
    protected $_saved = false;

    /**
     * Save all seted giftmessages
     *
     * @return Mage_Adminhtml_Model_Giftmessage_Save
     */
    public function saveAllInQuote()
    {
        $giftmessages = $this->getGiftmessages();

        if (!is_array($giftmessages)) {
            return $this;
        }

        foreach ($giftmessages as $entityId=>$giftmessage) {
            $this->_saveOne($entityId, $giftmessage);
        }

        return $this;
    }

    public function getSaved()
    {
        return $this->_saved;
    }

    public function saveAllInOrder()
    {
        $giftmessages = $this->getGiftmessages();

        if (!is_array($giftmessages)) {
            return $this;
        }

        foreach ($giftmessages as $entityId=>$giftmessage) {
            $this->_saveOne($entityId, $giftmessage);
        }

        return $this;
    }

    /**
     * Save a single gift message
     *
     * @param integer $entityId
     * @param array $giftmessage
     * @return Mage_Adminhtml_Model_Giftmessage_Save
     */
    protected function _saveOne($entityId, $giftmessage) {
        /* @var $giftmessageModel Mage_Giftmessage_Model_Message */
        $giftmessageModel = Mage::getModel('Mage_GiftMessage_Model_Message');
        $entityType = $this->_getMappedType($giftmessage['type']);

        switch($entityType) {
            case 'quote':
                $entityModel = $this->_getQuote();
                break;

            case 'quote_item':
                $entityModel = $this->_getQuote()->getItemById($entityId);
                break;

            default:
                $entityModel = $giftmessageModel->getEntityModelByType($entityType)
                    ->load($entityId);
                break;
        }

        if (!$entityModel) {
            return $this;
        }

        if ($entityModel->getGiftMessageId()) {
            $giftmessageModel->load($entityModel->getGiftMessageId());
        }

        $giftmessageModel->addData($giftmessage);

        if ($giftmessageModel->isMessageEmpty() && $giftmessageModel->getId()) {
            // remove empty giftmessage
            $this->_deleteOne($entityModel, $giftmessageModel);
            $this->_saved = false;
        } elseif (!$giftmessageModel->isMessageEmpty()) {
            $giftmessageModel->save();
            $entityModel->setGiftMessageId($giftmessageModel->getId());
            if($entityType != 'quote') {
                $entityModel->save();
            }
            $this->_saved = true;
        }

        return $this;
    }

    /**
     * Delete a single gift message from entity
     *
     * @param Mage_GiftMessage_Model_Message|null $giftmessageModel
     * @param Varien_Object $entityModel
     * @return Mage_Adminhtml_Model_Giftmessage_Save
     */
    protected function _deleteOne($entityModel, $giftmessageModel=null)
    {
        if($giftmessageModel === null) {
            $giftmessageModel = Mage::getModel('Mage_GiftMessage_Model_Message')
                ->load($entityModel->getGiftMessageId());
        }
        $giftmessageModel->delete();
        $entityModel->setGiftMessageId(0)
            ->save();
        return $this;
    }

    /**
     * Set allowed quote items for gift messages
     *
     * @param array $items
     * @return Mage_Adminhtml_Model_Giftmessage_Save
     */
    public function setAllowQuoteItems($items)
    {
        $this->_getSession()->setAllowQuoteItemsGiftMessage($items);
        return $this;
    }

    /**
     * Add allowed quote item for gift messages
     *
     * @param int $item
     * @return Mage_Adminhtml_Model_Giftmessage_Save
     */
    public function addAllowQuoteItem($item)
    {
        $items = $this->getAllowQuoteItems();
        if (!in_array($item, $items)) {
            $items[] = $item;
        }
        $this->setAllowQuoteItems($items);

        return $this;
    }

    /**
     * Retrive allowed quote items for gift messages
     *
     * @return array
     */
    public function getAllowQuoteItems()
    {
        if(!is_array($this->_getSession()->getAllowQuoteItemsGiftMessage())) {
            $this->setAllowQuoteItems(array());
        }

        return $this->_getSession()->getAllowQuoteItemsGiftMessage();
    }

    /**
     * Retrive allowed quote items products for gift messages
     *
     * @return array
     */
    public function getAllowQuoteItemsProducts()
    {
        $result = array();
        foreach ($this->getAllowQuoteItems() as $itemId) {
            $item = $this->_getQuote()->getItemById($itemId);
            if(!$item) {
                continue;
            }
            $result[] = $item->getProduct()->getId();
        }
        return $result;
    }

    /**
     * Checks allowed quote item for gift messages
     *
     * @param  Varien_Object $item
     * @return boolean
     */
    public function getIsAllowedQuoteItem($item)
    {
        if(!in_array($item->getId(), $this->getAllowQuoteItems())) {
            if ($item->getGiftMessageId() && $this->isGiftMessagesAvailable($item)) {
                $this->addAllowQuoteItem($item->getId());
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * Retrieve is gift message available for item (product)
     *
     * @param Varien_Object $item
     * @return bool
     */
    public function isGiftMessagesAvailable($item)
    {
        return Mage::helper('Mage_GiftMessage_Helper_Message')->getIsMessagesAvailable(
            'item', $item, $item->getStore()
        );
    }

    /**
     * Imports quote items for gift messages from products data
     *
     * @param unknown_type $products
     * @return unknown
     */
    public function importAllowQuoteItemsFromProducts($products)
    {
        $allowedItems = $this->getAllowQuoteItems();
        $deleteAllowedItems = array();
        foreach ($products as $productId=>$data) {
            $product = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStore($this->_getSession()->getStore())
                ->load($productId);
            $item = $this->_getQuote()->getItemByProduct($product);

            if(!$item) {
                continue;
            }

            if (in_array($item->getId(), $allowedItems)
                && !isset($data['giftmessage'])) {
                $deleteAllowedItems[] = $item->getId();
            } elseif (!in_array($item->getId(), $allowedItems)
                      && isset($data['giftmessage'])) {
                $allowedItems[] = $item->getId();
            }

        }

        $allowedItems = array_diff($allowedItems, $deleteAllowedItems);

        $this->setAllowQuoteItems($allowedItems);
        return $this;
    }

    public function importAllowQuoteItemsFromItems($items)
    {
        $allowedItems = $this->getAllowQuoteItems();
        $deleteAllowedItems = array();
        foreach ($items as $itemId=>$data) {

            $item = $this->_getQuote()->getItemById($itemId);

            if(!$item) {
                // Clean not exists items
                $deleteAllowedItems[] = $itemId;
                continue;
            }

            if (in_array($item->getId(), $allowedItems)
                && !isset($data['giftmessage'])) {
                $deleteAllowedItems[] = $item->getId();
            } elseif (!in_array($item->getId(), $allowedItems)
                      && isset($data['giftmessage'])) {
                $allowedItems[] = $item->getId();
            }

        }

        $allowedItems = array_diff($allowedItems, $deleteAllowedItems);
        $this->setAllowQuoteItems($allowedItems);
        return $this;
    }

    /**
     * Retrive mapped type for entity
     *
     * @param string $type
     * @return string
     */
    protected function _getMappedType($type)
    {
        $map = array(
            'main'          =>  'quote',
            'item'          =>  'quote_item',
            'order'         =>  'order',
            'order_item'    =>  'order_item'
        );

        if (isset($map[$type])) {
            return $map[$type];
        }

        return null;
    }

    /**
     * Retrieve session object
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Session_Quote');
    }

    /**
     * Retrieve quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getSession()->getQuote();
    }

}
