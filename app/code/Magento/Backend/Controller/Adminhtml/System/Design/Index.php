<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

/**
 * Class \Magento\Backend\Controller\Adminhtml\System\Design\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Backend\Controller\Adminhtml\System\Design
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
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
