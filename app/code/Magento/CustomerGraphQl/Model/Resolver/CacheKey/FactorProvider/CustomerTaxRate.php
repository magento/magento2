<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\ResourceModel\GroupRepository as CustomerGroupRepository;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\GenericFactorProviderInterface;
use Magento\Tax\Model\Calculation as CalculationModel;
use Magento\Tax\Model\ResourceModel\Calculation as CalculationResource;

/**
 * Provides tax rate as a factor to use in the cache key for resolver cache.
 */
class CustomerTaxRate implements GenericFactorProviderInterface
{
    private const NAME = 'CUSTOMER_TAX_RATE';

    /**
     * @var CustomerGroupRepository
     */
    private $groupRepository;

    /**
     * @var CalculationModel
     */
    private $calculationModel;

    /**
     * @var CalculationResource
     */
    private $calculationResource;

    /**
     * @param CustomerGroupRepository $groupRepository
     * @param CalculationModel $calculationModel
     * @param CalculationResource $calculationResource
     */
    public function __construct(
        CustomerGroupRepository $groupRepository,
        CalculationModel $calculationModel,
        CalculationResource $calculationResource
    ) {
        $this->groupRepository = $groupRepository;
        $this->calculationModel = $calculationModel;
        $this->calculationResource = $calculationResource;
    }

    /**
     * @inheritdoc
     */
    public function getFactorName(): string
    {
        return static::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getFactorValue(ContextInterface $context): string
    {
        $customerId = $context->getExtensionAttributes()->getIsCustomer()
            ? (int)$context->getUserId()
            : 0;
        $customerTaxClassId = $this->groupRepository->getById(
            $context->getExtensionAttributes()->getCustomerGroupId() ?? GroupInterface::NOT_LOGGED_IN_ID
        )->getTaxClassId();
        $rateRequest = $this->calculationModel->getRateRequest(
            null,
            null,
            $customerTaxClassId,
            $context->getExtensionAttributes()->getStore(),
            $customerId
        );
        $rateInfo = $this->calculationResource->getRateInfo($rateRequest);
        return (string)$rateInfo['value'];
    }
}
