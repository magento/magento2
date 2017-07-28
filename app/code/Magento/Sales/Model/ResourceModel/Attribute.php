<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\AbstractModel;

/**
 * Class \Magento\Sales\Model\ResourceModel\Attribute
 *
 * @since 2.0.0
 */
class Attribute
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    protected $connection;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * @param AppResource $resource
     * @param EventManager $eventManager
     * @since 2.0.0
     */
    public function __construct(
        AppResource $resource,
        EventManager $eventManager
    ) {
        $this->resource = $resource;
        $this->eventManager = $eventManager;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    protected function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resource->getConnection('sales');
        }
        return $this->connection;
    }

    /**
     * Before save object attribute
     *
     * @param AbstractModel $object
     * @param string $attribute
     * @return \Magento\Sales\Model\ResourceModel\Attribute
     * @since 2.0.0
     */
    protected function _beforeSaveAttribute(AbstractModel $object, $attribute)
    {
        if ($object->getEventObject() && $object->getEventPrefix()) {
            $this->eventManager->dispatch(
                $object->getEventPrefix() . '_save_attribute_before',
                [
                    $object->getEventObject() => $this,
                    'object' => $object,
                    'attribute' => $attribute
                ]
            );
        }
        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param AbstractModel $object
     * @param string $attribute
     * @return $this
     * @throws \Exception
     * @since 2.0.0
     */
    public function saveAttribute(AbstractModel $object, $attribute)
    {
        if ($attribute instanceof AbstractAttribute) {
            $attributes = $attribute->getAttributeCode();
        } elseif (is_string($attribute)) {
            $attributes = [$attribute];
        } else {
            $attributes = $attribute;
        }
        if (is_array($attributes) && !empty($attributes)) {
            $this->getConnection()->beginTransaction();
            $data = array_intersect_key($object->getData(), array_flip($attributes));
            try {
                $this->_beforeSaveAttribute($object, $attributes);
                if ($object->getId() && !empty($data)) {
                    $this->getConnection()->update(
                        $object->getResource()->getMainTable(),
                        $data,
                        [$object->getResource()->getIdFieldName() . '= ?' => (int)$object->getId()]
                    );
                    $object->addData($data);
                }
                $this->_afterSaveAttribute($object, $attributes);
                $this->getConnection()->commit();
            } catch (\Exception $e) {
                $this->getConnection()->rollBack();
                throw $e;
            }
        }
        return $this;
    }

    /**
     * After save object attribute
     *
     * @param AbstractModel $object
     * @param string $attribute
     * @return \Magento\Sales\Model\ResourceModel\Attribute
     * @since 2.0.0
     */
    protected function _afterSaveAttribute(AbstractModel $object, $attribute)
    {
        if ($object->getEventObject() && $object->getEventPrefix()) {
            $this->eventManager->dispatch(
                $object->getEventPrefix() . '_save_attribute_after',
                [
                    $object->getEventObject() => $this,
                    'object' => $object,
                    'attribute' => $attribute
                ]
            );
        }
        return $this;
    }
}
