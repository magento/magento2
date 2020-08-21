<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Customer;

use Magento\Framework\AuthorizationInterface;

/**
 * Class to invalidate user credentials
 */
class AuthorizationComposite implements AuthorizationInterface
{
    /**
     * @var AuthorizationInterface[]
     */
    private $authorizationChecks;

    public function __construct(
        array $authorizationChecks
    ) {
        $this->authorizationChecks = $authorizationChecks;
    }

    public function isAllowed($resource, $privilege = null)
    {
        $result = false;

        foreach ($this->authorizationChecks as $authorizationCheck) {
            $result = $authorizationCheck->isAllowed($resource, $privilege);
            if (!$result) {
                break;
            }
        }

        return $result;
    }
}
