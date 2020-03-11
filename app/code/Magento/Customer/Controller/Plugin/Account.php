<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Plugin;

use Closure;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Plugin verifies permissions using Action Name against injected (`fontend/di.xml`) rules
 */
class Account
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $allowedActions = [];
    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param ActionFlag $actionFlag
     * @param array $allowedActions List of actions that are allowed for not authorized users
     */
    public function __construct(
        RequestInterface $request,
        Session $customerSession,
        ActionFlag $actionFlag,
        array $allowedActions = []
    ) {
        $this->session = $customerSession;
        $this->allowedActions = $allowedActions;
        $this->request = $request;
        $this->actionFlag = $actionFlag;
    }

    /**
     * Executes original method if allowed, otherwise - redirects to log in
     *
     * @param AccountInterface $controllerAction
     * @param Closure $proceed
     * @return ResultInterface|ResponseInterface|void
     */
    public function aroundExecute(AccountInterface $controllerAction, Closure $proceed)
    {
        if ($this->isActionAllowed()) {
            $this->session->setNoReferer(true);
            $response = $proceed();
            $this->session->unsNoReferer(false);

            return $response;
        }

        if (!$this->session->authenticate()) {
            $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * Validates whether currently requested action is one of the allowed
     *
     * @return bool
     */
    private function isActionAllowed(): bool
    {
        $action = strtolower($this->request->getActionName());
        $pattern = '/^(' . implode('|', $this->allowedActions) . ')$/i';

        return (bool)preg_match($pattern, $action);
    }
}
