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
 * @category    Magento
 * @package     Magento_GiftMessage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Gift message inline edit form
 *
 * @category   Magento
 * @package    Magento_GiftMessage
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GiftMessage\Block\Message;

class Inline extends \Magento\Core\Block\Template
{
    protected $_entity = null;
    protected $_type   = null;
    protected $_giftMessage = null;

    protected $_template = 'inline.phtml';

    /**
     * Gift message message
     *
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $_giftMessageMessage = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\GiftMessage\Helper\Message $giftMessageMessage
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\GiftMessage\Helper\Message $giftMessageMessage,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_giftMessageMessage = $giftMessageMessage;
        $this->_customerSession = $customerSession;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Set entity
     *
     * @param $entity
     * @return \Magento\GiftMessage\Block\Message\Inline
     */
    public function setEntity($entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Get entity
     *
     * @return \Magento\GiftMessage\Block\Message\Inline
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return \Magento\GiftMessage\Block\Message\Inline
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
     * @return \Magento\GiftMessage\Block\Message\Inline
     */
    protected function _initMessage()
    {
        $this->_giftMessage = $this->helper('Magento\GiftMessage\Helper\Message')->getGiftMessage(
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
        if ($this->_customerSession->isLoggedIn()) {
            return $this->_customerSession->getCustomer()->getName();
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
                    $this->helper('Magento\GiftMessage\Helper\Message')->getGiftMessage($entity->getGiftMessageId())
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
            $this->_eventManager->dispatch('gift_options_prepare_items', array('items' => $entityItems));

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
        return $this->_giftMessageMessage->isMessagesAvailable('quote', $this->getEntity());
    }

    /**
     * Check availability of giftmessages for specified entity item
     *
     * @param $item
     * @return bool
     */
    public function isItemMessagesAvailable($item)
    {
        $type = substr($this->getType(), 0, 5) == 'multi' ? 'address_item' : 'item';
        return $this->_giftMessageMessage->isMessagesAvailable($type, $item);
    }

    /**
     * Product thumbnail image url getter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getThumbnailUrl($product)
    {
        return (string)$this->helper('Magento\Catalog\Helper\Image')->init($product, 'thumbnail')
            ->resize($this->getThumbnailSize());
    }

    /**
     * Thumbnail image size getter
     *
     * @return int
     */
    public function getThumbnailSize()
    {
        return $this->getVar('product_thumbnail_image_size', 'Magento_Catalog');
    }
}
