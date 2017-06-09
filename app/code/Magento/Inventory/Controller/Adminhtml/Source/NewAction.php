<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Source;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends \Magento\Cms\Controller\Adminhtml\Block
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::source';

    /**
     * {@inheritdoc}
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
