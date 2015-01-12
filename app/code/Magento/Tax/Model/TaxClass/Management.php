<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\TaxClass;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterface;

class Management implements \Magento\Tax\Api\TaxClassManagementInterface
{
    /**
     * Filter Builder
     *
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Search Criteria Builder
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Tax class repository
     *
     * @var \Magento\Tax\Model\TaxClass\Repository
     */
    protected $classRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param Repository $classRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        \Magento\Tax\Model\TaxClass\Repository $classRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->classRepository = $classRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxClassId($taxClassKey, $taxClassType = self::TYPE_PRODUCT)
    {
        if (!empty($taxClassKey)) {
            switch ($taxClassKey->getType()) {
                case TaxClassKeyInterface::TYPE_ID:
                    return $taxClassKey->getValue();
                case TaxClassKeyInterface::TYPE_NAME:
                    $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                        [$this->filterBuilder->setField(TaxClassInterface::KEY_TYPE)->setValue($taxClassType)->create()]
                    )->addFilter(
                        [
                            $this->filterBuilder->setField(TaxClassInterface::KEY_NAME)
                                ->setValue($taxClassKey->getValue())
                                ->create(),
                        ]
                    )->create();
                    $taxClasses = $this->classRepository->getList($searchCriteria)->getItems();
                    $taxClass = array_shift($taxClasses);
                    return (null == $taxClass) ? null : $taxClass->getClassId();
            }
        }
        return null;
    }
}
