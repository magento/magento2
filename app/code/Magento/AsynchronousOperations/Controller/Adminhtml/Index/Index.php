<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Logging::system_magento_logging_bulk_operations';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var string
     */
    private $menuId;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param string $menuId
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        $menuId = 'Magento_AsynchronousOperations::system_magento_logging_bulk_operations'
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->menuId = $menuId;
        parent::__construct($context);
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
        $this->_setActiveMenu($this->menuId);
        $resultPage->getConfig()->getTitle()->prepend(__('Bulk Actions Log'));
        return $resultPage;
    }
}
