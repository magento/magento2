<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\App\Action;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Introduces Context information for ActionInterface of Customer Action
 */
class ContextPlugin
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @param Session $customerSession
     * @param HttpContext $httpContext
     */
    public function __construct(Session $customerSession, HttpContext $httpContext)
    {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    /**
     * Set customer group and customer session id to HTTP context
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $subject)
    {
        $this->httpContext->setValue(
            Context::CONTEXT_GROUP,
            (string)$this->customerSession->getCustomerGroupId(),
            GroupManagement::NOT_LOGGED_IN_ID
        );
        $this->httpContext->setValue(
            Context::CONTEXT_AUTH,
            $this->customerSession->isLoggedIn(),
            false
        );
    }
}
