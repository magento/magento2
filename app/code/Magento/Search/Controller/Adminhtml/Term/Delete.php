<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Framework\Exception\NotFoundException;
use Magento\Search\Controller\Adminhtml\Term as TermController;
use Magento\Framework\Controller\ResultFactory;

class Delete extends TermController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        $id = (int)$this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($id) {
            try {
                $model = $this->_objectManager->create(\Magento\Search\Model\Query::class);
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the search.'));
                $resultRedirect->setPath('search/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('search/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return $resultRedirect;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a search term to delete.'));
        $resultRedirect->setPath('search/*/');
        return $resultRedirect;
    }
}
