<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class Delete
 *
 * @package Magento\Customer\Controller\Adminhtml\Group
 */
class Delete extends \Magento\Customer\Controller\Adminhtml\Group implements HttpPostActionInterface
{

    /**
     * Delete customer group.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $groupId = $this->getRequest()->getParam('id');
        if ($groupId) {
            try {
                $this->groupRepository->deleteById($groupId);
                $this->messageManager->addSuccessMessage(__('You deleted the Group.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $groupId]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a Group to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
