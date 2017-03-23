<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\TaxClass\Source;

use Magento\Framework\Exception\StateException;
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
