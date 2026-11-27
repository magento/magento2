<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\CacheIdFactorProviders;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\GroupRegistry;
use Magento\Customer\Model\ResourceModel\GroupRepository as CustomerGroupRepository;
use Magento\Framework\App\ObjectManager;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;
use Magento\Tax\Model\Calculation as CalculationModel;
use Magento\Tax\Model\ResourceModel\Calculation as CalculationResource;

/**
 * Provides the relevant parts of the address used for tax rate calculations as a factor to use in the cache id
 */
class CustomerTaxRateProvider implements CacheIdFactorProviderInterface
{
    public const NAME = 'CUSTOMER_TAX_RATE';

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
     * @var GroupRegistry
     */
    private $groupRegistry;

    /**
     * @param CustomerGroupRepository $groupRepository
     * @param CalculationModel $calculationModel
     * @param CalculationResource $calculationResource
     * @param GroupRegistry|null $groupRegistry
     */
    public function __construct(
        CustomerGroupRepository $groupRepository,
        CalculationModel $calculationModel,
        CalculationResource $calculationResource,
        ?GroupRegistry $groupRegistry = null
    ) {
        $this->groupRepository = $groupRepository;
        $this->calculationModel = $calculationModel;
        $this->calculationResource = $calculationResource;
        $this->groupRegistry = $groupRegistry ?? ObjectManager::getInstance()->get(GroupRegistry::class);
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
        $loggedIn = $context->getExtensionAttributes()->getIsCustomer();
        $customerId = $loggedIn ? (int)$context->getUserId() : 0;
        $customerGroupId = $context->getExtensionAttributes()->getCustomerGroupId() ?? GroupInterface::NOT_LOGGED_IN_ID;
        $customerTaxClassId = $this->groupRegistry->retrieve((int)$customerGroupId)->getTaxClassId();
        $store = $context->getExtensionAttributes()->getStore();
        $rateRequest = $this->calculationModel->getRateRequest(null, null, $customerTaxClassId, $store, $customerId);
        $rateInfo = $this->calculationResource->getRateInfo($rateRequest);
        return (string)$rateInfo['value'];
    }
}
