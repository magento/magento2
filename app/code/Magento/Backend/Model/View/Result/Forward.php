<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Result;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;

/**
 * @api
 * @since 2.0.0
 */
class Forward extends \Magento\Framework\Controller\Result\Forward
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
     * @param RequestInterface $request
     * @param Session $session
     * @param ActionFlag $actionFlag
     * @since 2.0.0
     */
    public function __construct(RequestInterface $request, Session $session, ActionFlag $actionFlag)
    {
        $this->session = $session;
        $this->actionFlag = $actionFlag;
        parent::__construct($request);
    }

    /**
     * @param string $action
     * @return $this
     * @since 2.0.0
     */
    public function forward($action)
    {
        $this->session->setIsUrlNotice($this->actionFlag->get('', AbstractAction::FLAG_IS_URLS_CHECKED));
        return parent::forward($action);
    }
}
