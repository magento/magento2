<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\ValidateCustomerAuthorization;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Customers Revoke Token resolver, used for GraphQL request processing.
 */
class RevokeCustomerToken implements ResolverInterface
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var ValidateCustomerAuthorization
     */
    private $validateCustomerAuthorization;

    /**
     * @param CustomerTokenServiceInterface $customerTokenService
     * @param ValidateCustomerAuthorization|null $validateCustomerAuthorization
     */
    public function __construct(
        CustomerTokenServiceInterface $customerTokenService,
        ?ValidateCustomerAuthorization $validateCustomerAuthorization = null
    ) {
        $this->customerTokenService = $customerTokenService;
        $this->validateCustomerAuthorization = $validateCustomerAuthorization
            ?? ObjectManager::getInstance()->get(ValidateCustomerAuthorization::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        /** @var ContextInterface $context */
        $this->validateCustomerAuthorization->execute($context);

        return ['result' => $this->customerTokenService->revokeCustomerAccessToken($context->getUserId())];
    }
}
