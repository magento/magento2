<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\TaxClass\Type;

use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;
use Magento\Customer\Api\Data\GroupInterface as CustomerGroup;

/**
 * Customer Tax Class
 */
class Customer extends \Magento\Tax\Model\TaxClass\AbstractType
{
    /**
     * @var CustomerGroupRepository
     */
    protected $customerGroupRepository;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Class Type
     *
     * @var string
     */
    protected $_classType = \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER;

    /**
     * @param \Magento\Tax\Model\Calculation\Rule $calculationRule
     * @param CustomerGroupRepository $customerGroupRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Model\Calculation\Rule $calculationRule,
        CustomerGroupRepository $customerGroupRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = array()
    ) {
        parent::__construct($calculationRule, $data);
        $this->customerGroupRepository = $customerGroupRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function isAssignedToObjects()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                [
                    $this->filterBuilder->setField(CustomerGroup::TAX_CLASS_ID)->setValue($this->getId())->create()
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
     * @return string
     */
    public function getObjectTypeName()
    {
        return __('customer group');
    }
}
