<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\TaxClass;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\TaxClass\Repository as TaxClassRepository;

class Management implements TaxClassManagementInterface
{
    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder Search Criteria Builder
     * @param FilterBuilder $filterBuilder Filter Builder
     * @param Repository $classRepository Tax class repository
     */
    public function __construct(
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly FilterBuilder $filterBuilder,
        protected readonly TaxClassRepository $classRepository
    ) {
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
                    $searchCriteria = $this->searchCriteriaBuilder->addFilters(
                        [$this->filterBuilder->setField(ClassModel::KEY_TYPE)->setValue($taxClassType)->create()]
                    )->addFilters(
                        [
                            $this->filterBuilder->setField(ClassModel::KEY_NAME)
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
