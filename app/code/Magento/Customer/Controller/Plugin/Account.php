<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;

class Account
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var array
     */
    private $allowedActions = [];

    /**
     * @param Session $customerSession
     * @param array $allowedActions List of actions that are allowed for not authorized users
     */
    public function __construct(
        Session $customerSession,
        array $allowedActions = []
    ) {
        $this->session = $customerSession;
        $this->allowedActions = $allowedActions;
    }

    /**
     * Dispatch actions allowed for not authorized users
     *
     * @param ActionInterface $subject
     * @param \Closure $proceed
     * @param RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        ActionInterface $subject,
        \Closure $proceed,
        RequestInterface $request
    ) {
        if (!$request->isDispatched()) {
            $proceed($request);
        }

        $action = strtolower($request->getActionName());
        $pattern = '/^(' . implode('|', $this->allowedActions) . ')$/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->session->authenticate($subject)) {
                $subject->getActionFlag()->set('', 'no-dispatch', true);
            }
        } else {
            $this->session->setNoReferer(true);
        }

        $result = $proceed($request);
        $this->session->unsNoReferer(false);
        return $result;
    }
}