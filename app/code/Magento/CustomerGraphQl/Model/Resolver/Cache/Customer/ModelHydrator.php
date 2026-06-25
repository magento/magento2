<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache\Customer;

use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Data\CustomerFactory;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\HydratorInterface;

/**
 * Customer resolver data hydrator to rehydrate propagated model.
 */
class ModelHydrator implements HydratorInterface, ResetAfterRequestInterface
{
    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;

    /**
     * @var Customer[]
     */
    private array $customerModels = [];

    /**
     * @var HydratorPool
     */
    private HydratorPool $hydratorPool;

    /**
     * @param CustomerFactory $customerFactory
     * @param HydratorPool $hydratorPool
     */
    public function __construct(
        CustomerFactory $customerFactory,
        HydratorPool $hydratorPool
    ) {
        $this->hydratorPool = $hydratorPool;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(array &$resolverData): void
    {
        if (isset($this->customerModels[$resolverData['model_id']])) {
            $resolverData['model'] = $this->customerModels[$resolverData['model_id']];
        } else {
            $hydrator = $this->hydratorPool->getHydrator($resolverData['model_entity_type']);
            $model = $this->customerFactory->create();
            $hydrator->hydrate($model, $resolverData['model_data']);
            $this->customerModels[$resolverData['model_id']] = $model;
            $resolverData['model'] = $this->customerModels[$resolverData['model_id']];
        }
    }

    /**
     * Reset customerModels
     *
     * @return void
     */
    public function _resetState(): void
    {
        $this->customerModels = [];
    }
}
