<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Get authentication header for customer
 */
class GetCustomerAuthenticationHeader
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @param CustomerTokenServiceInterface $customerTokenService
     */
    public function __construct(CustomerTokenServiceInterface $customerTokenService)
    {
        $this->customerTokenService = $customerTokenService;
    }

    /**
     * Get header to perform customer authenticated request
     *
     * @param string $email
     * @param string $password
     * @return string[]
     * @throws AuthenticationException
     */
    public function execute(string $email = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
