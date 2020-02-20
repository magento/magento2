<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Controller\Adminhtml\Bulk;

/**
 * Class View Operation Details Controller
 */
class Details extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\AsynchronousOperations\Model\AccessValidator
     */
    private $accessValidator;

    /**
     * @var string
     */
    private $menuId;

    /**
     * Details constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\AsynchronousOperations\Model\AccessValidator $accessValidator
     * @param string $menuId
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\AsynchronousOperations\Model\AccessValidator $accessValidator,
        $menuId = 'Magento_AsynchronousOperations::system_magento_logging_bulk_operations'
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accessValidator = $accessValidator;
        $this->menuId = $menuId;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Logging::system_magento_logging_bulk_operations')
            && $this->accessValidator->isAllowed($this->getRequest()->getParam('uuid'));
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
