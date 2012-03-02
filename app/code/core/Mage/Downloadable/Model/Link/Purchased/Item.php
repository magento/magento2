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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Downloadable links purchased item model
 *
 * @method Mage_Downloadable_Model_Resource_Link_Purchased_Item _getResource()
 * @method Mage_Downloadable_Model_Resource_Link_Purchased_Item getResource()
 * @method int getPurchasedId()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setPurchasedId(int $value)
 * @method int getOrderItemId()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setOrderItemId(int $value)
 * @method int getProductId()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setProductId(int $value)
 * @method string getLinkHash()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setLinkHash(string $value)
 * @method int getNumberOfDownloadsBought()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setNumberOfDownloadsBought(int $value)
 * @method int getNumberOfDownloadsUsed()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setNumberOfDownloadsUsed(int $value)
 * @method int getLinkId()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setLinkId(int $value)
 * @method string getLinkTitle()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setLinkTitle(string $value)
 * @method int getIsShareable()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setIsShareable(int $value)
 * @method string getLinkUrl()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setLinkUrl(string $value)
 * @method string getLinkFile()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setLinkFile(string $value)
 * @method string getLinkType()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setLinkType(string $value)
 * @method string getStatus()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setStatus(string $value)
 * @method string getCreatedAt()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Mage_Downloadable_Model_Link_Purchased_Item setUpdatedAt(string $value)
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_Model_Link_Purchased_Item extends Mage_Core_Model_Abstract
{
    const XML_PATH_ORDER_ITEM_STATUS = 'catalog/downloadable/order_item_status';

    const LINK_STATUS_PENDING   = 'pending';
    const LINK_STATUS_AVAILABLE = 'available';
    const LINK_STATUS_EXPIRED   = 'expired';
    const LINK_STATUS_PENDING_PAYMENT = 'pending_payment';
    const LINK_STATUS_PAYMENT_REVIEW = 'payment_review';

    /**
     * Enter description here...
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Downloadable_Model_Resource_Link_Purchased_Item');
        parent::_construct();
    }

    /**
     * Check order item id
     *
     * @return Mage_Core_Model_Abstract
     */
    public function _beforeSave()
    {
        if (null == $this->getOrderItemId()) {
            throw new Exception(
                Mage::helper('Mage_Downloadable_Helper_Data')->__('Order item id cannot be null'));
        }
        return parent::_beforeSave();
    }

}
