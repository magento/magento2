<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Customer account controller
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Account extends \Magento\Framework\App\Action\Action
{
    /**
     * List of actions that are allowed for not authorized users
     *
     * @var string[]
     */
    protected $openActions = [
        'create',
        'login',
        'logoutsuccess',
        'forgotpassword',
        'forgotpasswordpost',
        'resetpassword',
        'resetpasswordpost',
        'confirm',
        'confirmation',
        'createpassword',
        'createpost',
        'loginpost',
    ];

    /** @var Session */
    protected $session;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param RedirectFactory $resultRedirectFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        RedirectFactory $resultRedirectFactory,
        PageFactory $resultPageFactory
    ) {
        $this->session = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session model object
     *
     * @return Session
     */
    protected function _getSession()
    {
        return $this->session;
    }

    /**
     * Get list of actions that are allowed for not authorized users
     *
     * @return string[]
     */
    protected function getAllowedActions()
    {
        return $this->openActions;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->getRequest()->isDispatched()) {
            parent::dispatch($request);
        }

        $action = strtolower($this->getRequest()->getActionName());
        $pattern = '/^(' . implode('|', $this->getAllowedActions()) . ')$/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->_actionFlag->set('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
        $result = parent::dispatch($request);
        $this->_getSession()->unsNoReferer(false);
        return $result;
    }
}
