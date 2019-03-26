<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;

/**
 * Check customer password
 */
class CheckCustomerPassword
{
    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @param AuthenticationInterface $authentication
     */
    public function __construct(
        AuthenticationInterface $authentication
    ) {
        $this->authentication = $authentication;
    }

    /**
     * Check customer password
     *
     * @param string $password
     * @param int $customerId
     * @throws GraphQlAuthenticationException
     */
    public function execute(string $password, int $customerId)
    {
        try {
            $this->authentication->authenticate($customerId, $password);
        } catch (InvalidEmailOrPasswordException $e) {
            throw new GraphQlAuthenticationException(
                __('The password doesn\'t match this account. Verify the password and try again.')
            );
        }
    }
}
