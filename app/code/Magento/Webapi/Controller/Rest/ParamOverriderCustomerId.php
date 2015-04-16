<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;

/**
 * Replaces a "%customer_id%" value with the real customer id
 */
class ParamOverriderCustomerId implements ParamOverriderInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    public function __construct(UserContextInterface $userContext) {
        $this->userContext = $userContext;
    }

    public function getOverridenValue() {
        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
            return $this->userContext->getUserId();
        }
        return null;
    }
}
