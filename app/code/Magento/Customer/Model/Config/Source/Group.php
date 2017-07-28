<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config\Source;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Customer\Attribute\Source\GroupSourceLoggedInOnlyInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class \Magento\Customer\Model\Config\Source\Group
 *
 * @since 2.0.0
 */
class Group implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_options;

    /**
     * @deprecated 2.2.0
     * @var GroupManagementInterface
     * @since 2.0.0
     */
    protected $_groupManagement;

    /**
     * @deprecated 2.2.0
     * @var \Magento\Framework\Convert\DataObject
     * @since 2.0.0
     */
    protected $_converter;

    /**
     * @var GroupSourceLoggedInOnlyInterface
     * @since 2.2.0
     */
    private $groupSourceLoggedInOnly;

    /**
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\Convert\DataObject $converter
     * @param GroupSourceLoggedInOnlyInterface $groupSourceForLoggedInCustomers
     * @since 2.0.0
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Convert\DataObject $converter,
        GroupSourceLoggedInOnlyInterface $groupSourceForLoggedInCustomers = null
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
        $this->groupSourceLoggedInOnly = $groupSourceForLoggedInCustomers
            ?: ObjectManager::getInstance()->get(GroupSourceLoggedInOnlyInterface::class);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->groupSourceLoggedInOnly->toOptionArray();
            array_unshift($this->_options, ['value' => '', 'label' => __('-- Please Select --')]);
        }

        return $this->_options;
    }
}
