<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Resource;

/**
 * Customer group resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Group extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Group Management
     *
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var \Magento\Customer\Model\Resource\Customer\CollectionFactory
     */
    protected $_customersFactory;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param Customer\CollectionFactory $customersFactory
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Customer\Model\Resource\Customer\CollectionFactory $customersFactory
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_customersFactory = $customersFactory;
        parent::__construct($resource);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('customer_group', 'customer_group_id');
    }

    /**
     * Initialize unique fields
     *
     * @return $this
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
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $group)
    {
        if ($group->usesAsDefault()) {
            throw new \Magento\Framework\Model\Exception(__('The group "%1" cannot be deleted', $group->getCode()));
        }
        return parent::_beforeDelete($group);
    }

    /**
     * Method set default group id to the customers collection
     *
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return $this
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
     * @return \Magento\Customer\Model\Resource\Customer\Collection
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
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $group)
    {
        /** @var \Magento\Customer\Model\Group $group */
        $group->setCode(substr($group->getCode(), 0, $group::GROUP_CODE_MAX_LENGTH));
        return parent::_beforeSave($group);
    }
}
