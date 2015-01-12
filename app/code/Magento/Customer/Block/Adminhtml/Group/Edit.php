<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Group;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Customer group edit block
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupManagementInterface $groupManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        GroupRepositoryInterface $groupRepository,
        GroupManagementInterface $groupManagement,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->groupRepository = $groupRepository;
        $this->groupManagement = $groupManagement;
        parent::__construct($context, $data);
    }

    /**
     * Update Save and Delete buttons. Remove Delete button if group can't be deleted.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_group';
        $this->_blockGroup = 'Magento_Customer';

        $this->buttonList->update('save', 'label', __('Save Customer Group'));
        $this->buttonList->update('delete', 'label', __('Delete Customer Group'));

        $groupId = $this->coreRegistry->registry(RegistryConstants::CURRENT_GROUP_ID);
        if (!$groupId || $this->groupManagement->isReadonly($groupId)) {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve the header text, either editing an existing group or creating a new one.
     *
     * @return string
     */
    public function getHeaderText()
    {
        $groupId = $this->coreRegistry->registry(RegistryConstants::CURRENT_GROUP_ID);
        if (is_null($groupId)) {
            return __('New Customer Group');
        } else {
            $group = $this->groupRepository->getById($groupId);
            return __('Edit Customer Group "%1"', $this->escapeHtml($group->getCode()));
        }
    }

    /**
     * Retrieve CSS classes added to the header.
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'icon-head head-customer-groups';
    }
}
