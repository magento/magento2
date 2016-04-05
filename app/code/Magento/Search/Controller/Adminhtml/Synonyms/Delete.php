<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Magento\Search\Controller\Adminhtml\Synonyms;

/**
 * Delete Controller
 */
class Delete extends Synonyms
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('group_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->synonymGroupModel->load($id);
                $repository = $this->_objectManager->create('Magento\Search\Api\SynonymGroupRepositoryInterface');
                $repository->delete($this->synonymGroupModel);
                $this->messageManager->addSuccess(__('The synonym group has been deleted.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->logger->error($e);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('An error was encountered while performing delete operation.'));
                $this->logger->error($e);
            }
        } else {
            $this->messageManager->addError(__('We can\'t find a synonym group to delete.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
