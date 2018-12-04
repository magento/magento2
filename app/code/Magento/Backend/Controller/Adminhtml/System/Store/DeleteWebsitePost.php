<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\NotFoundException;

class DeleteWebsitePost extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
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
        $model = $this->_objectManager->create(\Magento\Store\Model\Website::class);
        $model->load($itemId);
        if (!$model) {
            $this->messageManager->addErrorMessage(__('Something went wrong. Please try again.'));
            return $redirectResult->setPath('adminhtml/*/');
        }
        if (!$model->isCanDelete()) {
            $this->messageManager->addErrorMessage(__('This website cannot be deleted.'));
            return $redirectResult->setPath('adminhtml/*/editWebsite', ['website_id' => $model->getId()]);
        }

        if (!$this->_backupDatabase()) {
            return $redirectResult->setPath('*/*/editWebsite', ['website_id' => $itemId]);
        }

        try {
            $model->delete();
            $this->messageManager->addSuccessMessage(__('You deleted the website.'));
            return $redirectResult->setPath('adminhtml/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to delete the website. Please try again later.'));
        }
        return $redirectResult->setPath('*/*/editWebsite', ['website_id' => $itemId]);
    }
}
