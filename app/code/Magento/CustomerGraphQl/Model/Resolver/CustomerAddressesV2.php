<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\CustomerGraphQl\Model\ValidateAddressRequest;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Formatter\CustomerAddresses as AddressFormatter;

/**
 * Provides data for customer.addressesV2 with pagination
 */
class CustomerAddressesV2 implements ResolverInterface
{
    /**
     * CustomerAddressesV2 Constructor
     *
     * @param AddressRepositoryInterface $addressRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AddressFormatter $addressesFormatter
     * @param ValidateAddressRequest $validateAddressRequest
     */
    public function __construct(
        private readonly AddressRepositoryInterface $addressRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly AddressFormatter $addressesFormatter,
        private readonly ValidateAddressRequest $validateAddressRequest
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        $this->validateAddressRequest->execute($value, $args);

        /** @var Customer $customer */
        $customer = $value['model'];

        try {
            $this->searchCriteriaBuilder->addFilter('parent_id', (int)$customer->getId());
            $this->searchCriteriaBuilder->setCurrentPage($args['currentPage']);
            $this->searchCriteriaBuilder->setPageSize($args['pageSize']);
            $searchResult =  $this->addressRepository->getList($this->searchCriteriaBuilder->create());
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $this->addressesFormatter->format($searchResult);
    }
}
