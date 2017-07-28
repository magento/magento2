<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;

/**
 * Customer group resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Group extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb
{
    /**
     * Group Management
     *
     * @var \Magento\Customer\Api\GroupManagementInterface
     * @since 2.0.0
     */
    protected $_groupManagement;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     * @since 2.0.0
     */
    protected $_customersFactory;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Snapshot $entitySnapshot,
     * @param RelationComposite $entityRelationComposite,
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param Customer\CollectionFactory $customersFactory
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customersFactory,
        $connectionName = null
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_customersFactory = $customersFactory;
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('customer_group', 'customer_group_id');
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [['field' => 'customer_group_code', 'title' => __('Customer Group')]];

        return $this;
    }

    /**
     * Check if group uses as default
     *
     * @param  \Magento\Framework\Model\AbstractModel $group
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $group)
    {
        if ($group->usesAsDefault()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can\'t delete group "%1".', $group->getCode())
            );
        }
        return parent::_beforeDelete($group);
    }

    /**
     * Method set default group id to the customers collection
     *
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return $this
     * @since 2.0.0
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $group)
    {
        $customerCollection = $this->_createCustomersCollection()->addAttributeToFilter(
            'group_id',
            $group->getId()
        )->load();
        foreach ($customerCollection as $customer) {
            /** @var $customer \Magento\Customer\Model\Customer */
            $customer->load($customer->getId());
            $defaultGroupId = $this->_groupManagement->getDefaultGroup($customer->getStoreId())->getId();
            $customer->setGroupId($defaultGroupId);
            $customer->save();
        }
        return parent::_afterDelete($group);
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     * @since 2.0.0
     */
    protected function _createCustomersCollection()
    {
        return $this->_customersFactory->create();
    }

    /**
     * Prepare data before save
     *
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $group)
    {
        /** @var \Magento\Customer\Model\Group $group */
        $group->setCode(substr($group->getCode(), 0, $group::GROUP_CODE_MAX_LENGTH));
        return parent::_beforeSave($group);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId() == \Magento\Customer\Model\Group::CUST_GROUP_ALL) {
            $this->skipReservedId($object);
        }

        return $this;
    }

    /**
     * Here we do not allow to save systems reserved ID.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @since 2.2.0
     */
    private function skipReservedId(\Magento\Framework\Model\AbstractModel $object)
    {
        $tableFieldsWithoutIdField = $this->getTableFieldsWithoutIdField();
        $select = $this->getConnection()->select();
        $select->from(
            [$this->getMainTable()],
            $tableFieldsWithoutIdField
        )
            ->where('customer_group_id = ?', \Magento\Customer\Model\Group::CUST_GROUP_ALL);

        $query = $this->getConnection()->insertFromSelect(
            $select,
            $this->getMainTable(),
            $tableFieldsWithoutIdField
        );
        $this->getConnection()->query($query);
        $lastInsertId = $this->getConnection()->lastInsertId();

        $query = $this->getConnection()->deleteFromSelect(
            $select,
            $this->getMainTable()
        );
        $this->getConnection()->query($query);

        $object->setId($lastInsertId);
    }

    /**
     * Get main table fields except of ID field.
     *
     * @return array
     * @since 2.2.0
     */
    private function getTableFieldsWithoutIdField()
    {
        $fields = $this->getConnection()->describeTable($this->getMainTable());
        if (isset($fields['customer_group_id'])) {
            unset($fields['customer_group_id']);
        }

        return array_keys($fields);
    }
}
