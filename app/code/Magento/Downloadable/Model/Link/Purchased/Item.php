<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Link\Purchased;

use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item as Resource;

/**
 * Downloadable links purchased item model
 *
 * @method int getPurchasedId()
 * @method Item setPurchasedId($value)
 * @method int getOrderItemId()
 * @method Item setOrderItemId($value)
 * @method int getProductId()
 * @method Item setProductId($value)
 * @method string getLinkHash()
 * @method Item setLinkHash($value)
 * @method int getNumberOfDownloadsBought()
 * @method Item setNumberOfDownloadsBought($value)
 * @method int getNumberOfDownloadsUsed()
 * @method Item setNumberOfDownloadsUsed($value)
 * @method int getLinkId()
 * @method Item setLinkId($value)
 * @method string getLinkTitle()
 * @method Item setLinkTitle($value)
 * @method int getIsShareable()
 * @method Item setIsShareable($value)
 * @method string getLinkUrl()
 * @method Item setLinkUrl($value)
 * @method string getLinkFile()
 * @method Item setLinkFile($value)
 * @method string getLinkType()
 * @method Item setLinkType($value)
 * @method string getStatus()
 * @method Item setStatus($value)
 * @method string getCreatedAt()
 * @method Item setCreatedAt($value)
 * @method string getUpdatedAt()
 * @method Item setUpdatedAt($value)
 *
 * @api
 * @since 100.0.2
 */
class Item extends \Magento\Framework\Model\AbstractModel
{
    const XML_PATH_ORDER_ITEM_STATUS = 'catalog/downloadable/order_item_status';

    const LINK_STATUS_PENDING = 'pending';

    const LINK_STATUS_AVAILABLE = 'available';

    const LINK_STATUS_EXPIRED = 'expired';

    const LINK_STATUS_PENDING_PAYMENT = 'pending_payment';

    const LINK_STATUS_PAYMENT_REVIEW = 'payment_review';

    /**
     * Enter description here...
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item::class);
        parent::_construct();
    }

    /**
     * Check order item id
     *
     * @return $this
     * @throws \Exception
     */
    public function beforeSave()
    {
        if (null == $this->getOrderItemId()) {
            throw new \Exception(__('Order item id cannot be null'));
        }
        return parent::beforeSave();
    }
}
