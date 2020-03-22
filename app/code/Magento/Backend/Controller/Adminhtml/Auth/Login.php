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
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\Page as ResultPage;
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
     * @return ResultRedirect|ResultPage
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
     * Returns Redirect response object
     *
     * @param string $path
     * @return ResultRedirect
     */
    private function getRedirect(string $path): ResultRedirect
    {
        $resultRedirect = $this->redirectFactory->create();
        return $resultRedirect->setPath($path);
    }
}
