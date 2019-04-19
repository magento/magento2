<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Framework\Exception\NotFoundException;
use Magento\Search\Controller\Adminhtml\Term as TermController;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends TermController
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

        $searchIds = $this->getRequest()->getParam('search');
        if (!is_array($searchIds)) {
            $this->messageManager->addErrorMessage(__('Please select searches.'));
        } else {
            try {
                foreach ($searchIds as $searchId) {
                    $model = $this->_objectManager->create(\Magento\Search\Model\Query::class)->load($searchId);
                    $model->delete();
                }
                $this->messageManager->addSuccessMessage(__('Total of %1 record(s) were deleted.', count($searchIds)));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('search/*/');
        return $resultRedirect;
    }
}
