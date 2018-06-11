<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;

class DeleteStorePost extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * Delete store view post action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var HttpRequest $request */
        $request = $this->getRequest();
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
        $redirectResult = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );
        if (!$request->isPost()) {
            throw new NotFoundException(__('Page not found.'));
        }

        $itemId = $request->getParam('item_id');
        if (!($model = $this->_objectManager->create('Magento\Store\Model\Store')->load($itemId))) {
            $this->messageManager->addError(__('Something went wrong. Please try again.'));
            return $redirectResult->setPath('adminhtml/*/');
        }
        if (!$model->isCanDelete()) {
            $this->messageManager->addError(__('This store view cannot be deleted.'));
            return $redirectResult->setPath('adminhtml/*/editStore', ['store_id' => $model->getId()]);
        }

        if (!$this->_backupDatabase()) {
            return $redirectResult->setPath('*/*/editStore', ['store_id' => $itemId]);
        }

        try {
            $model->delete();

            $this->_eventManager->dispatch('store_delete', ['store' => $model]);

            $this->messageManager->addSuccess(__('You deleted the store view.'));
            return $redirectResult->setPath('adminhtml/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to delete the store view. Please try again later.'));
        }
        return $redirectResult->setPath('adminhtml/*/editStore', ['store_id' => $itemId]);
    }
}
