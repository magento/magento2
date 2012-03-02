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
 * @package     Mage_GiftMessage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Gift message inline edit form
 *
 * @category   Mage
 * @package    Mage_GiftMessage
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_GiftMessage_Block_Message_Inline extends Mage_Core_Block_Template
{

    protected $_entity = null;
    protected $_type   = null;
    protected $_giftMessage = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('inline.phtml');
    }

    /**
     * Set entity
     *
     * @return mixed
     */
    public function setEntity($entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Get entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Mage_GiftMessage_Block_Message_Inline
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Check if entity has gift message
     *
     * @return bool
     */
    public function hasGiftMessage()
    {
        return $this->getEntity()->getGiftMessageId() > 0;
    }

    /**
     * Init message
     *
     * @return Mage_GiftMessage_Block_Message_Inline
     */
    protected function _initMessage()
    {
        $this->_giftMessage = $this->helper('Mage_GiftMessage_Helper_Message')->getGiftMessage(
            $this->getEntity()->getGiftMessageId()
        );
        return $this;
    }

    /**
     * Get default value for From field
     *
     * @return string
     */
    public function getDefaultFrom()
    {
        if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            return Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer()->getName();
        } else {
            return $this->getEntity()->getBillingAddress()->getName();
        }
    }

    /**
     * Get default value for To field
     *
     * @return string
     */
    public function getDefaultTo()
    {
        if ($this->getEntity()->getShippingAddress()) {
            return $this->getEntity()->getShippingAddress()->getName();
        } else {
            return $this->getEntity()->getName();
        }
    }

    /**
     * Retrieve message
     *
     * @param mixed $entity
     * @return string
     */
    public function getMessage($entity=null)
    {
        if (is_null($this->_giftMessage)) {
            $this->_initMessage();
        }

        if ($entity) {
            if (!$entity->getGiftMessage()) {
                $entity->setGiftMessage(
                    $this->helper('Mage_GiftMessage_Helper_Message')->getGiftMessage($entity->getGiftMessageId())
                );
            }
            return $entity->getGiftMessage();
        }

        return $this->_giftMessage;
    }

    /**
     * Retrieve items
     *
     * @return array
     */
    public function getItems()
    {
        if (!$this->getData('items')) {
            $items = array();

            $entityItems = $this->getEntity()->getAllItems();
            Mage::dispatchEvent('gift_options_prepare_items', array('items' => $entityItems));

            foreach ($entityItems as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($this->isItemMessagesAvailable($item) || $item->getIsGiftOptionsAvailable()) {
                    $items[] = $item;
                }
            }
            $this->setData('items', $items);
        }
        return $this->getData('items');
    }

    /**
     * Retrieve additional url
     *
     * @return bool
     */
    public function getAdditionalUrl()
    {
        return $this->getUrl('*/*/getAdditional');
    }

    /**
     * Check if items are available
     *
     * @return bool
     */
    public function isItemsAvailable()
    {
        return count($this->getItems()) > 0;
    }

    /**
     * Return items count
     *
     * @return int
     */
    public function countItems()
    {
        return count($this->getItems());
    }

    /**
     * Check if items has messages
     *
     * @return bool
     */
    public function getItemsHasMesssages()
    {
        foreach ($this->getItems() as $item) {
            if ($item->getGiftMessageId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if entity has message
     *
     * @return bool
     */
    public function getEntityHasMessage()
    {
        return $this->getEntity()->getGiftMessageId() > 0;
    }

    /**
     * Return escaped value
     *
     * @param string $value
     * @param string $defaultValue
     * @return string
     */
    public function getEscaped($value, $defaultValue='')
    {
        return $this->escapeHtml(trim($value)!='' ? $value : $defaultValue);
    }

    /**
     * Check availability of giftmessages for specified entity
     *
     * @return bool
     */
    public function isMessagesAvailable()
    {
        return Mage::helper('Mage_GiftMessage_Helper_Message')->isMessagesAvailable('quote', $this->getEntity());
    }

    /**
     * Check availability of giftmessages for specified entity item
     *
     * @return bool
     */
    public function isItemMessagesAvailable($item)
    {
        $type = substr($this->getType(), 0, 5) == 'multi' ? 'address_item' : 'item';
        return Mage::helper('Mage_GiftMessage_Helper_Message')->isMessagesAvailable($type, $item);
    }

    /**
     * Product thumbnail image url getter
     *
     * @param Mage_Core_Model_Product $product
     * @return string
     */
    public function getThumbnailUrl($product)
    {
        return (string) $this->helper('Mage_Catalog_Helper_Image')->init($product, 'thumbnail')->resize($this->getThumbnailSize());
    }

    /**
     * Thumbnail image size getter
     *
     * @return int
     */
    public function getThumbnailSize()
    {
        return $this->getVar('product_thumbnail_image_size', 'Mage_Catalog');
    }

}
