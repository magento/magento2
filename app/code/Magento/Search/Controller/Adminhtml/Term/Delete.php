<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Search\Controller\Adminhtml\Term as TermController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Search\Model\Query as ModelQuery;

class Delete extends TermController implements HttpPostActionInterface
{
    /**
     * @return ResultRedirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var ResultRedirect $redirectResult */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($id) {
            try {
                $model = $this->_objectManager->create(ModelQuery::class);
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
