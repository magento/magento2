<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Plugin;

use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Plugin verifies whether current User is eligible to access the page that implements AccountInterface
 */
class AccountControllerPermissionsCheck
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
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var array
     */
    private $allowedActions = [];

    /**
     * @param Session $customerSession
     * @param RequestInterface $request
     * @param ActionFlag $actionFlag
     * @param array $allowedActions List of actions that are allowed for not authorized users
     */
    public function __construct(
        Session $customerSession,
        RequestInterface $request,
        ActionFlag $actionFlag,
        array $allowedActions = []
    ) {
        $this->session = $customerSession;
        $this->request = $request;
        $this->actionFlag = $actionFlag;
        $this->allowedActions = $allowedActions;
    }

    /**
     * Dispatch actions allowed for not authorized users
     *
     * @param AccountInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(AccountInterface $subject)
    {
        $action = strtolower($this->request->getActionName());
        $pattern = '/^(' . implode('|', $this->allowedActions) . ')$/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->session->authenticate()) {
                $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
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
