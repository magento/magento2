<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

/**
 * @api
 * @since 2.0.0
 */
class Login extends \Magento\Backend\Controller\Adminhtml\Auth
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
        } else {
            return $this->resultPageFactory->create();
        }
    }

    /**
     * Get redirect response
     *
     * @param string $path
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    private function getRedirect($path)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($path);
        return $resultRedirect;
    }
}
