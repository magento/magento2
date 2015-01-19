<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Cart\Product\Composite\Cart;

class Update extends \Magento\Customer\Controller\Adminhtml\Cart\Product\Composite\Cart
{
    /**
     * IFrame handler for submitted configuration for quote item
     *
     * @return void
     */
    public function execute()
    {
        $updateResult = new \Magento\Framework\Object();
        try {
            $this->_initData();

            $buyRequest = new \Magento\Framework\Object($this->getRequest()->getParams());
            $this->_quote->updateItem($this->_quoteItem->getId(), $buyRequest);
            $this->_quote->collectTotals();
            $this->quoteRepository->save($this->_quote);

            $updateResult->setOk(true);
        } catch (\Exception $e) {
            $updateResult->setError(true);
            $updateResult->setMessage($e->getMessage());
        }

        $updateResult->setJsVarName($this->getRequest()->getParam('as_js_varname'));
        $this->_objectManager->get('Magento\Backend\Model\Session')->setCompositeProductResult($updateResult);
        $this->_redirect('catalog/product/showUpdateResult');
    }
}
