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
class CustomerModelHydrator implements HydratorInterface
{
    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;

    /**
     * @var array
     */
    private array $customerModels = [];

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
        if (isset($this->customerModels[$resolverData['model_id']])) {
            $resolverData['model'] = $this->customerModels[$resolverData['model_id']];
        } else {
            $this->customerModels[$resolverData['model_id']] = $this->customerFactory->create(
                ['data' => $resolverData]
            );
            $this->customerModels[$resolverData['model_id']]->setId($resolverData['model_id']);
            $this->customerModels[$resolverData['model_id']]->setData('group_id', $resolverData['model_group_id']);
            $resolverData['model'] = $this->customerModels[$resolverData['model_id']];
        }

    }
}
