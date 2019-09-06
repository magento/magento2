<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Logging::system_magento_logging_bulk_operations';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var string
     */
    private $menuId;

    /**
     * Details constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param string $menuId
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        $menuId = 'Magento_AsynchronousOperations::system_magento_logging_bulk_operations'
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->menuId = $menuId;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed();
    }

    /**
     * Bulk list action
     *
     * @return \Magento\Framework\View\Result\Page
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
