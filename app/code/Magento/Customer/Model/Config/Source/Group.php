<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config\Source;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Customer\Source\GroupSourceForLoggedInCustomersInterface;
use Magento\Framework\App\ObjectManager;

class Group implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @deprecated
     * @var GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @deprecated
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_converter;

    /**
     * @var GroupSourceForLoggedInCustomersInterface
     */
    private $groupSourceForLoggedInCustomers;

    /**
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\Convert\DataObject $converter
     * @param GroupSourceForLoggedInCustomersInterface $groupSourceForLoggedInCustomers
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Convert\DataObject $converter,
        GroupSourceForLoggedInCustomersInterface $groupSourceForLoggedInCustomers = null
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
        $this->groupSourceForLoggedInCustomers = $groupSourceForLoggedInCustomers
            ?: ObjectManager::getInstance()->get(GroupSourceForLoggedInCustomersInterface::class);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->groupSourceForLoggedInCustomers->toOptionArray();
            array_unshift($this->_options, ['value' => '', 'label' => __('-- Please Select --')]);
        }

        return $this->_options;
    }
}
