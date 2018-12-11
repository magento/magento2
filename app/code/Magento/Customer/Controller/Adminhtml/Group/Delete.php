<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class Delete
 *
 * @package Magento\Customer\Controller\Adminhtml\Group
 */
class Delete extends \Magento\Customer\Controller\Adminhtml\Group
{

    /**
     * Delete customer group.
     *
     * @return Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }
        /** @var Redirect $resultRedirect */
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
