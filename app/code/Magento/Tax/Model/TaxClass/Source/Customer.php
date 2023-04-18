<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\TaxClass\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\StateException;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Model\ClassModel;

/**
 * Customer tax class source model.
 */
class Customer extends AbstractSource
{
    /**
     * Initialize dependencies.
     *
     * @param TaxClassRepositoryInterface $taxClassRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        protected readonly TaxClassRepositoryInterface $taxClassRepository,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly FilterBuilder $filterBuilder
    ) {
    }

    /**
     * Retrieve all customer tax classes as an options array.
     *
     * @return array
     * @throws StateException
     */
    public function getAllOptions()
    {
        if (empty($this->_options)) {
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
            $this->_options = $options;
        }

        return $this->_options;
    }
}
