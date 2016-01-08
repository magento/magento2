<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $selected = $this->getRequest()->getParam('selected');
        if (empty($selected)) {
            $this->messageManager->addError(__('You haven\'t selected any items!'));
            return $resultRedirect->setPath('*/*/');
        }
        $model = $this->_objectManager->create('Magento\Search\Model\SynonymGroup');
        foreach ($selected as $itemId) {
            try {
                $model->load($itemId);
                $model->delete();
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', count($selected)));
        return $resultRedirect->setPath('*/*/');
    }
}
