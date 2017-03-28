<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

use Magento\Customer\Controller\RegistryConstants;

class NewAction extends \Magento\Customer\Controller\Adminhtml\Group
{
    /**
     * Initialize current group and set it in the registry.
     *
     * @return int
     */
    protected function _initGroup()
    {
        $groupId = $this->getRequest()->getParam('id');
        $this->_coreRegistry->register(RegistryConstants::CURRENT_GROUP_ID, $groupId);

        return $groupId;
    }

    /**
     * Edit or create customer group.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $groupId = $this->_initGroup();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Customer::customer_group');
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Groups'));
        $resultPage->addBreadcrumb(__('Customers'), __('Customers'));
        $resultPage->addBreadcrumb(__('Customer Groups'), __('Customer Groups'), $this->getUrl('customer/group'));

        if ($groupId === null) {
            $resultPage->addBreadcrumb(__('New Group'), __('New Customer Groups'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Customer Group'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Group'), __('Edit Customer Groups'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->groupRepository->getById($groupId)->getCode()
            );
        }

        $resultPage->getLayout()->addBlock(\Magento\Customer\Block\Adminhtml\Group\Edit::class, 'group', 'content')
            ->setEditMode((bool)$groupId);

        return $resultPage;
    }
}
