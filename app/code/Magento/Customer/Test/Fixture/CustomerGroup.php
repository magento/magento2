<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Fixture;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\Hydrator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Data fixture for customer group
 */
class CustomerGroup implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        GroupInterface::CODE => 'Customergroup%uniqid%',
        GroupInterface::TAX_CLASS_ID => 3,
    ];

    /**
     * @var ServiceFactory
     */
    private ServiceFactory $serviceFactory;

    /**
     * @var TaxClassRepositoryInterface
     */
    private TaxClassRepositoryInterface $taxClassRepository;

    /** @var Hydrator */
    private Hydrator $hydrator;

    /**
     * @param ServiceFactory $serviceFactory
     * @param TaxClassRepositoryInterface $taxClassRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Hydrator $hydrator
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        TaxClassRepositoryInterface $taxClassRepository,
        Hydrator $hydrator
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->taxClassRepository = $taxClassRepository;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Customer::DEFAULT_DATA.
     * @return DataObject|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function apply(array $data = []): ?DataObject
    {
        $customerGroupSaveService = $this->serviceFactory->create(
            GroupRepositoryInterface::class,
            'save'
        );
        $data = self::DEFAULT_DATA;
        if (!empty($data['tax_class_id'])) {
            $data[GroupInterface::TAX_CLASS_ID] = $this->taxClassRepository->get($data['tax_class_id'])->getClassId();
        }

        $customerGroup =  $customerGroupSaveService->execute(
            [
                'group' => $data,
            ]
        );

        return new DataObject($this->hydrator->extract($customerGroup));
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(GroupRepositoryInterface::class, 'deleteById');
        $service->execute(
            [
                'id' => $data->getId()
            ]
        );
    }
}
