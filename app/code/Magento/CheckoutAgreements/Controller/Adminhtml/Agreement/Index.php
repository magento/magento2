<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement implements HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addContent(
            $this->_view->getLayout()->createBlock(\Magento\CheckoutAgreements\Block\Adminhtml\Agreement::class)
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Terms and Conditions'));
        $this->_view->renderLayout();
    }
}
