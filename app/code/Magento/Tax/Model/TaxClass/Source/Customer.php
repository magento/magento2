<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\TaxClass\Source;

use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\ClassModel;

/**
 * Customer tax class source model.
 */
class Customer extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $taxClassRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->taxClassRepository = $taxClassRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function loadOptions(): array
    {
        $options = [];
        $filter = $this->filterBuilder->setField(ClassModel::KEY_TYPE)
            ->setValue(TaxClassManagementInterface::TYPE_CUSTOMER)
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();
        $searchResults = $this->taxClassRepository->getList($searchCriteria);
        foreach ($searchResults->getItems() as $taxClass) {
            $options[] = [
                'value' => $taxClass->getClassId(),
                'label' => $taxClass->getClassName(),
            ];
        }

        return $options;
    }
}
