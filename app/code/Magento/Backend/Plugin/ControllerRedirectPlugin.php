<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Plugin;

use Magento\Backend\App\ActionInterface;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;

class ControllerRedirectPlugin
{
    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var BackendSession
     */
    private $session;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @param BackendSession $session
     * @param RedirectInterface $redirect
     * @param UrlInterface $urlBuilder
     * @param ActionFlag $actionFlag
     */
    public function __construct(
        BackendSession $session,
        RedirectInterface $redirect,
        UrlInterface $urlBuilder,
        ActionFlag $actionFlag
    ) {
        $this->session = $session;
        $this->redirect = $redirect;
        $this->urlBuilder = $urlBuilder;
        $this->actionFlag = $actionFlag;
    }

    /**
     * Replaces Storefront implementation of Redirect method
     *
     * @param RedirectResult $redirect
     * @return RedirectResult
     */
    public function aroundSetRefererOrBaseUrl(RedirectResult $redirect)
    {
        $redirectUrl = $this->redirect->getRedirectUrl($this->getStartupUrl());
        return $redirect->setUrl($redirectUrl);
    }

    public function beforeRender(RedirectResult $redirect, HttpInterface $httpResponse)
    {
        $this->session->setIsUrlNotice($this->actionFlag->get('', ActionInterface::FLAG_IS_URLS_CHECKED));
    }

    /**
     * Returns default Admin URL
     *
     * @return string
     */
    private function getStartupUrl(): string
    {
        return $this->urlBuilder->getUrl($this->urlBuilder->getStartupPageUrl());
    }
}
