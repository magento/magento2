<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

use Magento\Framework\Exception\LocalizedException;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Save extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('adminhtml/*/');
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            /** @var $session \Magento\Backend\Model\Session */
            $session = $this->_objectManager->get('Magento\Backend\Model\Session');
            try {
                $model = $this->_getUrlRewrite();

                $requestPath = $this->getRequest()->getParam('request_path');
                $this->_objectManager->get('Magento\UrlRewrite\Helper\UrlRewrite')->validateRequestPath($requestPath);

                $model->setEntityType($this->getRequest()->getParam('entity_type') ?: \Magento\UrlRewrite\Model\Mode\Custom::ENTITY_TYPE)
                    ->setRequestPath($requestPath)
                    ->setTargetPath($this->getRequest()->getParam('target_path', $model->getTargetPath()))
                    ->setRedirectType($this->getRequest()->getParam('redirect_type'))
                    ->setStoreId($this->getRequest()->getParam('store_id', 0))
                    ->setDescription($this->getRequest()->getParam('description'));

                $this->_eventManager->dispatch('url_rewrite_save_handle', ['url_rewrite' => $model]);
                $model->save();

                $this->messageManager->addSuccess(__('The URL Rewrite has been saved.'));
                return $redirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $session->setUrlRewriteData($data);
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while saving URL Rewrite.'));
                $session->setUrlRewriteData($data);
            }
        }
        return $redirect;
    }
}
