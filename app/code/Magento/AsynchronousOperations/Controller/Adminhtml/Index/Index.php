<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use Magento\AsynchronousOperations\Model\AccessManager;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Index extends Action implements HttpGetActionInterface
{
    public const BULK_OPERATIONS_MENU_ID = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations";

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var AccessManager
     */
    private $accessManager;

    /**
     * Details constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param AccessManager $accessManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AccessManager $accessManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accessManager = $accessManager;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->accessManager->isOwnActionsAllowed();
    }

    /**
     * Bulk list action
     *
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->initLayout();
        $this->_setActiveMenu(self::BULK_OPERATIONS_MENU_ID);
        $resultPage->getConfig()->getTitle()->prepend(__('Bulk Actions Log'));
        return $resultPage;
    }
}
