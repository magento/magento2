<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Result;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App;
use Magento\Framework\App\ActionFlag;

class Redirect extends \Magento\Framework\Controller\Result\Redirect
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * Constructor
     *
     * @param App\Response\RedirectInterface $redirect
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     * @param Session $session
     * @param ActionFlag $actionFlag
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
     * {@inheritdoc}
     */
    protected function render(App\ResponseInterface $response)
    {
        $this->session->setIsUrlNotice($this->actionFlag->get('', AbstractAction::FLAG_IS_URLS_CHECKED));
        return parent::render($response);
    }
}
