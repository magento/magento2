<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Backend\Controller\Adminhtml\System\Design implements HttpGetActionInterface
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::system_design_schedule');
        $resultPage->getConfig()->getTitle()->prepend(__('Store Design'));
        return $resultPage;
    }
}
