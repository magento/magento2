<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Controller\Adminhtml\Bulk;

use Magento\AsynchronousOperations\Model\AccessManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Class View Operation Details Controller
 */
class Details extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var AccessManager
     */
    private $accessManager;

    /**
     * @var string
     */
    private $menuId;

    /**
     * Details constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param AccessManager $accessManager
     * @param string $menuId
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AccessManager $accessManager,
        $menuId = 'Magento_AsynchronousOperations::system_magento_logging_bulk_operations'
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accessManager = $accessManager;
        $this->menuId = $menuId;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->accessManager->isAllowedForBulkUuid($this->getRequest()->getParam('uuid'));
    }

    /**
     * Bulk details action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $bulkId = $this->getRequest()->getParam('uuid');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->initLayout();
        $this->_setActiveMenu($this->menuId);
        $resultPage->getConfig()->getTitle()->prepend(__('Action Details - #' . $bulkId));

        return $resultPage;
    }
}
