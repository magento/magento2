<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * @api
 * @since 100.0.2
 */
class Login implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var RequestInterface
     */

    private $request;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param Auth $auth
     * @param UrlInterface $backendUrl
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        RequestInterface $request,
        Auth $auth,
        UrlInterface $backendUrl,
        PageFactory $resultPageFactory,
        RedirectFactory $redirectFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->auth = $auth;
        $this->backendUrl = $backendUrl;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Administrator login action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->auth->isLoggedIn()) {
            if ($this->auth->getAuthStorage()->isFirstPageAfterLogin()) {
                $this->auth->getAuthStorage()->setIsFirstPageAfterLogin(true);
            }
            return $this->getRedirect($this->backendUrl->getStartupPageUrl());
        }

        $requestUrl = $this->request->getUri();
        $backendUrl = $this->backendUrl->getUrl('*');
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
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setPath($path);
        return $resultRedirect;
    }
}
