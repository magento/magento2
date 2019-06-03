<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Customers Revoke Token resolver, used for GraphQL request processing.
 */
class RevokeCustomerToken implements ResolverInterface
{
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @param GetCustomer $getCustomer
     * @param CustomerTokenServiceInterface $customerTokenService
     */
    public function __construct(
        GetCustomer $getCustomer,
        CustomerTokenServiceInterface $customerTokenService
    ) {
        $this->getCustomer = $getCustomer;
        $this->customerTokenService = $customerTokenService;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $customer = $this->getCustomer->execute($context);

        return ['result' => $this->customerTokenService->revokeCustomerAccessToken((int)$customer->getId())];
    }
}
