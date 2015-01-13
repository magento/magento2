<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Creditmemo;

use Magento\Sales\Model\Resource\Entity as SalesResource;
use Magento\Sales\Model\Spi\CreditmemoItemResourceInterface;

/**
 * Flat sales order creditmemo item resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Item extends SalesResource implements CreditmemoItemResourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_creditmemo_item_resource';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_creditmemo_item', 'entity_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /**@var $object \Magento\Sales\Model\Order\Creditmemo\Item*/
        if (!$object->getParentId() && $object->getCreditmemo()) {
            $object->setParentId($object->getCreditmemo()->getId());
        }
        return parent::_beforeSave($object);
    }
}
