<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

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
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(string $password, int $customerId)
    {
        try {
            $this->authentication->authenticate($customerId, $password);
        } catch (InvalidEmailOrPasswordException $e) {
            throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
        } catch (UserLockedException $e) {
            throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
    }
}
