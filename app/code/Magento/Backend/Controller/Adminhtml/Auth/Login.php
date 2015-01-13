<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

class Login extends \Magento\Backend\Controller\Adminhtml\Auth
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Administrator login action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->_auth->isLoggedIn()) {
            if ($this->_auth->getAuthStorage()->isFirstPageAfterLogin()) {
                $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(true);
            }
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($this->_backendUrl->getStartupPageUrl());
            return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }
}
