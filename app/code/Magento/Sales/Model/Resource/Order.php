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
namespace Magento\Sales\Model\Resource;

use Magento\Framework\Math\Random;
use Magento\Framework\App\Resource as AppResource;
use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Model\Increment as SalesIncrement;
use Magento\Sales\Model\Resource\Entity as SalesResource;
use Magento\Sales\Model\Resource\Order\Handler\State as StateHandler;
use Magento\Sales\Model\Resource\Order\Handler\Address as AddressHandler;
use Magento\Sales\Model\Resource\Order\Grid as OrderGrid;

/**
 * Flat sales order resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Order extends SalesResource
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_resource';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'resource';

    /**
     * @var StateHandler
     */
    protected $stateHandler;

    /**
     * @var AddressHandler
     */
    protected $addressHandler;

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_flat_order', 'entity_id');
    }

    /**
     * @param AppResource $resource
     * @param DateTime $dateTime
     * @param Attribute $attribute
     * @param SalesIncrement $salesIncrement
     * @param AddressHandler $addressHandler
     * @param StateHandler $stateHandler
     * @param OrderGrid $gridAggregator
     */
    public function __construct(
        AppResource $resource,
        DateTime $dateTime,
        Attribute $attribute,
        SalesIncrement $salesIncrement,
        AddressHandler $addressHandler,
        StateHandler $stateHandler,
        OrderGrid $gridAggregator
    ) {
        $this->stateHandler = $stateHandler;
        $this->addressHandler = $addressHandler;
        parent::__construct($resource, $dateTime, $attribute, $salesIncrement, $gridAggregator);
    }

    /**
     * Count existent products of order items by specified product types
     *
     * @param int $orderId
     * @param array $productTypeIds
     * @param bool $isProductTypeIn
     * @return array
     */
    public function aggregateProductsByTypes($orderId, $productTypeIds = array(), $isProductTypeIn = false)
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()->from(
            array('o' => $this->getTable('sales_flat_order_item')),
            array('o.product_type', new \Zend_Db_Expr('COUNT(*)'))
        )->joinInner(
            array('p' => $this->getTable('catalog_product_entity')),
            'o.product_id=p.entity_id',
            array()
        )->where(
            'o.order_id=?',
            $orderId
        )->group(
            'o.product_type'
        );
        if ($productTypeIds) {
            $select->where(sprintf('(o.product_type %s (?))', $isProductTypeIn ? 'IN' : 'NOT IN'), $productTypeIds);
        }
        return $adapter->fetchPairs($select);
    }

    /**
     * Process items dependency for new order, returns qty of affected items;
     *
     * @param \Magento\Sales\Model\Order $object
     * @return int
     */
    protected function calculateItems(\Magento\Sales\Model\Order $object)
    {
        $itemsCount = 0;
        if (!$object->getId()) {
            foreach ($object->getAllItems() as $item) {
                /** @var  \Magento\Sales\Model\Order\Item $item */
                $parent = $item->getQuoteParentItemId();
                if ($parent && !$item->getParentItem()) {
                    $item->setParentItem($object->getItemByQuoteItemId($parent));
                } elseif (!$parent) {
                    $itemsCount++;
                }
            }
        }
        return $itemsCount;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order $object */
        $this->addressHandler->removeEmptyAddresses($object);
        $this->stateHandler->check($object);
        if (!$object->getId()) {
            /** @var \Magento\Store\Model\Store $store */
            $store = $object->getStore();
            $name = [
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName()
            ];
            $object->setStoreName(implode("\n", $name));
        }
        $object->setTotalItemCount($this->calculateItems($object));
        $object->setData(
            'protect_code',
            substr(md5(uniqid(Random::getRandomNumber(), true) . ':' . microtime(true)), 5, 6)
        );
        $isNewCustomer = !$object->getCustomerId() || $object->getCustomerId() === true;
        if ($isNewCustomer && $object->getCustomer()) {
            $object->setCustomerId($object->getCustomer()->getId());
        }
        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order $object */
        $this->addressHandler->process($object);
        if (null !== $object->getItemsCollection()) {
            $object->getItemsCollection()->save();
        }
        if (null !== $object->getPaymentsCollection()) {
            $object->getPaymentsCollection()->save();
        }
        if (null !== $object->getStatusHistoryCollection()) {
            $object->getStatusHistoryCollection()->save();
        }
        foreach ($object->getRelatedObjects() as $relatedObject) {
            $relatedObject->save();
        }
        return parent::_afterSave($object);
    }
}
