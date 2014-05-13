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
namespace Magento\Customer\Model\Resource;

/**
 * Customer group resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Group extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Customer data
     *
     * @var \Magento\Customer\Helper\Data
     */
    protected $_customerData = null;

    /**
     * @var \Magento\Customer\Model\Resource\Customer\CollectionFactory
     */
    protected $_customersFactory;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Customer\Helper\Data $customerData
     * @param \Magento\Customer\Model\Resource\Customer\CollectionFactory $customersFactory
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Customer\Helper\Data $customerData,
        \Magento\Customer\Model\Resource\Customer\CollectionFactory $customersFactory
    ) {
        $this->_customerData = $customerData;
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
        $this->_uniqueFields = array(array('field' => 'customer_group_code', 'title' => __('Customer Group')));

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
            $defaultGroupId = $this->_customerData->getDefaultCustomerGroupId($customer->getStoreId());
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
}
