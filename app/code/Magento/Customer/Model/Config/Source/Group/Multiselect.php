<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config\Source\Group;

use Magento\Customer\Api\GroupManagementInterface;

class Multiselect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Customer groups options array
     *
     * @var null|array
     */
    protected $_options;

    /**
     * @var GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var \Magento\Framework\Convert\Object
     */
    protected $_converter;

    /**
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\Convert\Object $converter
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Convert\Object $converter
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
    }

    /**
     * Retrieve customer groups as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $groups = $this->_groupManagement->getLoggedInGroups();
            $this->_options = $this->_converter->toOptionArray($groups, 'id', 'code');
        }
        return $this->_options;
    }
}
