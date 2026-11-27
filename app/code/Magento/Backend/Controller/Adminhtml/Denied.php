<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Denied extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Magento_Backend::admin';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Render the access denied page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->_auth->isLoggedIn()) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setStatusHeader(403, '1.1', 'Forbidden');
            return $resultRedirect->setPath('*/auth/login');
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setStatusHeader(403, '1.1', 'Forbidden');
        $resultPage->addHandle('adminhtml_denied');
        return $resultPage;
    }
}
