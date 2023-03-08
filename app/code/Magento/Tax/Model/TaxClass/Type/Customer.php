<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxClass\Type;

use Magento\Customer\Api\Data\GroupInterface as CustomerGroup;
use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Phrase;
use Magento\Tax\Model\Calculation\Rule as CalculationRule;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\TaxClass\AbstractType as TaxClassAbstractType;

/**
 * Customer Tax Class
 */
class Customer extends TaxClassAbstractType
{
    /**
     * Class Type
     *
     * @var string
     */
    protected $_classType = TaxClassModel::TAX_CLASS_TYPE_CUSTOMER;

    /**
     * @param CalculationRule $calculationRule
     * @param CustomerGroupRepository $customerGroupRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        CalculationRule $calculationRule,
        protected readonly CustomerGroupRepository $customerGroupRepository,
        protected readonly FilterBuilder $filterBuilder,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($calculationRule, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function isAssignedToObjects()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters(
                [
                    $this->filterBuilder->setField(CustomerGroup::TAX_CLASS_ID)->setValue($this->getId())->create(),
                ]
            )
            ->create();
        $result = $this->customerGroupRepository->getList($searchCriteria);
        $items = $result->getItems();
        return !empty($items);
    }

    /**
     * Get Name of Objects that use this Tax Class Type
     *
     * @return Phrase
     */
    public function getObjectTypeName()
    {
        return __('customer group');
    }
}
