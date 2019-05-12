<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class Edit
 *
 * @package Magento\Customer\Controller\Adminhtml\Group
 */
class Edit extends \Magento\Customer\Controller\Adminhtml\Group implements HttpGetActionInterface
{
    /**
     * Edit customer group.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $groupId = $this->getRequest()->getParam('id');

        if (isset($groupId)) {
            $group = $this->groupRepository->getById($groupId);
            if (!$group->getCode()) {
                $this->messageManager->addErrorMessage(__('This Group no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $this->_coreRegistry->register('customer_group', $group);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            __('General'),
            __('General')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Groups'));
        $resultPage->getConfig()->getTitle()->prepend(__('General'));
        return $resultPage;
    }
}
