<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache;

use Magento\Customer\Model\Data\AddressFactory;
use Magento\Customer\Model\Data\CustomerFactory;
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
     * @var AddressFactory
     */
    private AddressFactory $addressFactory;

    /**
     * @var array
     */
    private array $customerModels = [];

    /**
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory
    ) {
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(array &$resolverData): void
    {
        if (isset($this->customerModels[$resolverData['model_id']])) {
            $resolverData['model'] = $this->customerModels[$resolverData['model_id']];
        } else {
            $model = $this->customerFactory->create(
                ['data' => $resolverData]
            );
            $model->setId($resolverData['model_id']);
            $model->setData('group_id', $resolverData['model_group_id']);
            // address array is a part of the model so restoring addresses from flat data
            $addresses = $model->getAddresses();
            foreach ($addresses as $key => $address) {
                $addresses[$key] = $this->addressFactory->create(
                    ['data' => $address]
                );
            }
            $model->setAddresses($addresses);
            $this->customerModels[$resolverData['model_id']] = $model;
            $resolverData['model'] = $this->customerModels[$resolverData['model_id']];
        }
    }
}
