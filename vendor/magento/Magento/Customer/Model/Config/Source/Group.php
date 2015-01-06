<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Model\Config\Source;

use Magento\Customer\Api\GroupManagementInterface;

class Group implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
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
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $groups = $this->_groupManagement->getLoggedInGroups();
            $this->_options = $this->_converter->toOptionArray($groups, 'id', 'code');
            array_unshift($this->_options, ['value' => '', 'label' => __('-- Please Select --')]);
        }
        return $this->_options;
    }
}
