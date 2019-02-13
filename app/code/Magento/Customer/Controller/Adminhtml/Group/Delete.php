<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;

class Delete extends \Magento\Customer\Controller\Adminhtml\Group
{
    /**
     * Delete customer group.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->groupRepository->deleteById($id);
                $this->messageManager->addSuccess(__('You deleted the customer group.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(__('The customer group no longer exists.'));
                return $resultRedirect->setPath('customer/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('customer/group/edit', ['id' => $id]);
            }
        }
        return $resultRedirect->setPath('customer/group');
    }
}
