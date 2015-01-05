<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;


class AddConfigured extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Adds configured product to quote
     *
     * @return void
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
        $updateResult = new \Magento\Framework\Object();
        if ($errorMessage) {
            $updateResult->setError(true);
            $updateResult->setMessage($errorMessage);
        } else {
            $updateResult->setOk(true);
        }

        $updateResult->setJsVarName($this->getRequest()->getParam('as_js_varname'));
        $this->_objectManager->get('Magento\Backend\Model\Session')->setCompositeProductResult($updateResult);
        $this->_redirect('catalog/product/showUpdateResult');
    }
}
