<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Plugin;

use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session;
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
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param array $allowedActions List of actions that are allowed for not authorized users
     */
    public function __construct(
        RequestInterface $request,
        Session $customerSession,
        array $allowedActions = []
    ) {
        $this->session = $customerSession;
        $this->allowedActions = $allowedActions;
        $this->request = $request;
    }

    /**
     * Dispatch actions allowed for not authorized users
     *
     * @param AccountInterface $subject
     * @return void
     */
    public function beforeExecute(AccountInterface $subject)
    {
        $action = strtolower($this->request->getActionName());
        $pattern = '/^(' . implode('|', $this->allowedActions) . ')$/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->session->authenticate()) {
                $subject->getActionFlag()->set('', ActionInterface::FLAG_NO_DISPATCH, true);
            }
        } else {
            $this->session->setNoReferer(true);
        }
    }

    /**
     * Remove No-referer flag from customer session
     *
     * @param AccountInterface $subject
     * @param ResponseInterface|ResultInterface $result
     * @return ResponseInterface|ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(AccountInterface $subject, $result)
    {
        $this->session->unsNoReferer(false);
        return $result;
    }
}
