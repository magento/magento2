<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer\Attribute\Source;

use Magento\Customer\Api\GroupManagementInterface;

/**
 * Customer group attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Group extends \Magento\Eav\Model\Entity\Attribute\Source\Table implements GroupSourceLoggedInOnlyInterface
{
    /**
     * @var GroupManagementInterface
     * @since 2.0.0
     */
    protected $_groupManagement;

    /**
     * @var \Magento\Framework\Convert\DataObject
     * @since 2.0.0
     */
    protected $_converter;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\Convert\DataObject $converter
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Convert\DataObject $converter
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $groups = $this->_groupManagement->getLoggedInGroups();
            $this->_options = $this->_converter->toOptionArray($groups, 'id', 'code');
        }

        return $this->_options;
    }
}
