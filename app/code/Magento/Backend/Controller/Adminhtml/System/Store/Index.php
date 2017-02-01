<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Backend::system_store');
        $resultPage->addBreadcrumb(__('Stores'), __('Stores'));
        $resultPage->addBreadcrumb(__('All Stores'), __('All Stores'));
        $resultPage->getConfig()->getTitle()->prepend(__('Stores'));
        return $resultPage;
    }
}
