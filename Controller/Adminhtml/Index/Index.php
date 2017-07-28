<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Controller\Adminhtml\Index;

/**
 * Class \Magento\AsynchronousOperations\Controller\Adminhtml\Index\Index
 *
 * @since 2.2.0
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.2.0
     */
    private $resultPageFactory;

    /**
     * Details constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     * @since 2.2.0
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Logging::system_magento_logging_bulk_operations');
    }
    
    /**
     * Bulk list action
     *
     * @return \Magento\Framework\View\Result\Page
     * @since 2.2.0
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->initLayout();
        $this->_setActiveMenu('Magento_Logging::system_magento_logging_events');
        $resultPage->getConfig()->getTitle()->prepend(__('Bulk Actions Log'));
        return $resultPage;
    }
}
