<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;

/**
 * Replaces a "%customer_id%" value with the real customer id
 * @since 2.0.0
 */
class ParamOverriderCustomerId implements ParamOverriderInterface
{
    /**
     * @var UserContextInterface
     * @since 2.0.0
     */
    private $userContext;

    /**
     * Constructs an object to override the customer ID parameter on a request.
     *
     * @param UserContextInterface $userContext
     * @since 2.0.0
     */
    public function __construct(UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function getOverriddenValue()
    {
        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
            return $this->userContext->getUserId();
        }
        return null;
    }
}
