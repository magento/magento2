<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Controller\Adminhtml\Stock;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * NewAction Controller
 */
class NewAction extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::stock';

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Inventory::stock');
        $resultPage->getConfig()->getTitle()->prepend(__('New Stock'));

        return $resultPage;
    }
}
