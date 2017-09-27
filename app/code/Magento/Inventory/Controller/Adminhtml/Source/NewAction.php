<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

/**
 * NewAction Controller
 */
class NewAction extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = Index::ADMIN_RESOURCE;

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Inventory::source');
        $resultPage->getConfig()->getTitle()->prepend(__('New Source'));
        return $resultPage;
    }
}
