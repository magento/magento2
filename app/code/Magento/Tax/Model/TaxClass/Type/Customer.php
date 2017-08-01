<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxClass\Type;

use Magento\Customer\Api\Data\GroupInterface as CustomerGroup;
use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;

/**
 * Customer Tax Class
 * @since 2.0.0
 */
class Customer extends \Magento\Tax\Model\TaxClass\AbstractType
{
    /**
     * @var CustomerGroupRepository
     * @since 2.0.0
     */
    protected $customerGroupRepository;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     * @since 2.0.0
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * Class Type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_classType = \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER;

    /**
     * @param \Magento\Tax\Model\Calculation\Rule $calculationRule
     * @param CustomerGroupRepository $customerGroupRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Tax\Model\Calculation\Rule $calculationRule,
        CustomerGroupRepository $customerGroupRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($calculationRule, $data);
        $this->customerGroupRepository = $customerGroupRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getObjectTypeName()
    {
        return __('customer group');
    }
}
