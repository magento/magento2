<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config\Source\Group;

use Magento\Customer\Model\Customer\Attribute\Source\GroupSourceLoggedInOnlyInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class \Magento\Customer\Model\Config\Source\Group\Multiselect
 *
 */
class Multiselect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Customer groups options array
     *
     * @var null|array
     */
    protected $_options;

    /**
     * @deprecated 100.2.0
     * @var GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @deprecated 100.2.0
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_converter;

    /**
     * @var GroupSourceLoggedInOnlyInterface
     */
    private $groupSourceLoggedInOnly;

    /**
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\Convert\DataObject $converter
     * @param GroupSourceLoggedInOnlyInterface|null $groupSourceLoggedInOnly
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Convert\DataObject $converter,
        GroupSourceLoggedInOnlyInterface $groupSourceLoggedInOnly = null
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
        $this->groupSourceLoggedInOnly = $groupSourceLoggedInOnly
            ?: ObjectManager::getInstance()->get(GroupSourceLoggedInOnlyInterface::class);
    }

    /**
     * Retrieve customer groups as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->groupSourceLoggedInOnly->toOptionArray();
        }
        return $this->_options;
    }
}
