<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache;

use Magento\Customer\Model\CustomerFactory;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\HydratorInterface;

/**
 * Customer resolver data hydrator to rehydrate propagated model.
 */
class ModelHydrator implements HydratorInterface
{
    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;

    /**
     * @param CustomerFactory $customerFactory
     */
    public function __construct(CustomerFactory $customerFactory)
    {
        $this->customerFactory = $customerFactory;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(array &$resolverData): void
    {
        $model = $this->customerFactory->create(['data' => $resolverData]);
        $model->setId($resolverData['model_id']);
        $model->setData('group_id', $resolverData['model_group_id']);
        $resolverData['model'] = $model;
    }
}
