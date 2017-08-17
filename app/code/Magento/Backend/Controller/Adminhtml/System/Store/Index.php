<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index returns Stores page
 */
class Index extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * Returns Stores page
     *
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
