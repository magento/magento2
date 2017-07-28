<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Result;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;

/**
 * @api
 * @since 2.0.0
 */
class Redirect extends \Magento\Framework\Controller\Result\Redirect
{
    /**
     * @var \Magento\Backend\Model\Session
     * @since 2.0.0
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ActionFlag
     * @since 2.0.0
     */
    protected $actionFlag;

    /**
     * Constructor
     *
     * @param App\Response\RedirectInterface $redirect
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     * @param Session $session
     * @param ActionFlag $actionFlag
     * @since 2.0.0
     */
    public function __construct(
        App\Response\RedirectInterface $redirect,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        Session $session,
        ActionFlag $actionFlag
    ) {
        $this->session = $session;
        $this->actionFlag = $actionFlag;
        parent::__construct($redirect, $urlBuilder);
    }

    /**
     * Set referer url or dashboard if referer does not exist
     *
     * @return $this
     * @since 2.0.0
     */
    public function setRefererOrBaseUrl()
    {
        $this->url = $this->redirect->getRedirectUrl($this->urlBuilder->getUrl($this->urlBuilder->getStartupPageUrl()));
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function render(HttpResponseInterface $response)
    {
        $this->session->setIsUrlNotice($this->actionFlag->get('', AbstractAction::FLAG_IS_URLS_CHECKED));
        return parent::render($response);
    }
}
