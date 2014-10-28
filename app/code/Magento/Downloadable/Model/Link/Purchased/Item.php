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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Model\Link\Purchased;

use Magento\Downloadable\Model\Resource\Link\Purchased\Item as Resource;

/**
 * Downloadable links purchased item model
 *
 * @method Resource _getResource()
 * @method Resource getResource()
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
 * @author      Magento Core Team <core@magentocommerce.com>
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
        $this->_init('Magento\Downloadable\Model\Resource\Link\Purchased\Item');
        parent::_construct();
    }

    /**
     * Check order item id
     *
     * @return $this
     * @throws \Exception
     */
    public function _beforeSave()
    {
        if (null == $this->getOrderItemId()) {
            throw new \Exception(__('Order item id cannot be null'));
        }
        return parent::_beforeSave();
    }
}
