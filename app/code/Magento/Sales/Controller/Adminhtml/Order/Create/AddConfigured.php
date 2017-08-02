<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Create\AddConfigured
 *
 * @since 2.0.0
 */
class AddConfigured extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Adds configured product to quote
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        $errorMessage = null;
        try {
            $this->_initSession()->_processData();
        } catch (\Exception $e) {
            $this->_reloadQuote();
            $errorMessage = $e->getMessage();
        }

        // Form result for client javascript
        $updateResult = new \Magento\Framework\DataObject();
        if ($errorMessage) {
            $updateResult->setError(true);
            $updateResult->setMessage($errorMessage);
        } else {
            $updateResult->setOk(true);
        }

        $updateResult->setJsVarName($this->getRequest()->getParam('as_js_varname'));
        $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setCompositeProductResult($updateResult);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('catalog/product/showUpdateResult');
    }
}
