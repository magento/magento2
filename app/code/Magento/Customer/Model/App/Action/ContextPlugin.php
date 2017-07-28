<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\App\Action;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Class ContextPlugin
 * @since 2.0.0
 */
class ContextPlugin
{
    /**
     * @var Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var HttpContext
     * @since 2.0.0
     */
    protected $httpContext;

    /**
     * @param Session $customerSession
     * @param HttpContext $httpContext
     * @since 2.0.0
     */
    public function __construct(Session $customerSession, HttpContext $httpContext)
    {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    /**
     * Set customer group and customer session id to HTTP context
     *
     * @param AbstractAction $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        $this->httpContext->setValue(
            Context::CONTEXT_GROUP,
            $this->customerSession->getCustomerGroupId(),
            GroupManagement::NOT_LOGGED_IN_ID
        );
        $this->httpContext->setValue(
            Context::CONTEXT_AUTH,
            $this->customerSession->isLoggedIn(),
            false
        );
    }
}
