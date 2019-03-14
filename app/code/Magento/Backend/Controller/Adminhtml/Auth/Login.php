<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGet;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPost;

/**
 * @api
 * @since 100.0.2
 */
class Login extends \Magento\Backend\Controller\Adminhtml\Auth implements HttpGet, HttpPost
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
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
            return $this->getRedirect($this->_backendUrl->getStartupPageUrl());
        }

        $requestUrl = $this->getRequest()->getUri();
        $backendUrl = $this->getUrl('*');
        // redirect according to rewrite rule
        if ($requestUrl != $backendUrl) {
            return $this->getRedirect($backendUrl);
        }
        return $this->resultPageFactory->create();
    }

    /**
     * Get redirect response
     *
     * @param string $path
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    private function getRedirect($path)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($path);
        return $resultRedirect;
    }
}
