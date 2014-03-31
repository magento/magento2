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

use Magento\Customer\Service\V1\Data\CustomerGroup;

/**
 * Customer Tax Class
 */
class Customer extends \Magento\Tax\Model\TaxClass\AbstractType
{
    /**
     * @var \Magento\Customer\Model\Group
     */
    protected $_modelCustomerGroup;

    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface
     */
    protected $groupService;

    /**
     * @var \Magento\Customer\Service\V1\Data\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Customer\Service\V1\Data\SearchCriteriaBuilder
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
     * @param \Magento\Customer\Model\Group $modelCustomerGroup
     * @param \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService
     * @param \Magento\Customer\Service\V1\Data\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Service\V1\Data\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Model\Calculation\Rule $calculationRule,
        \Magento\Customer\Model\Group $modelCustomerGroup,
        \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService,
        \Magento\Customer\Service\V1\Data\FilterBuilder $filterBuilder,
        \Magento\Customer\Service\V1\Data\SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = array()
    ) {
        parent::__construct($calculationRule, $data);
        $this->_modelCustomerGroup = $modelCustomerGroup;
        $this->groupService = $groupService;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get Customer Groups with this tax class
     *
     * @deprecated In favor of getAssignedDataObjects. This function should be removed once all the implementations of
     * \Magento\Tax\Model\TaxClass\Type\TypeInterface::getAssignedToObjects are refactored to return Data Objects.
     * Will be revisited in MAGETWO-21827
     *
     * @return \Magento\Model\Resource\Db\Collection\AbstractCollection
     */
    public function getAssignedToObjects()
    {
        return $this->_modelCustomerGroup->getCollection()->addFieldToFilter('tax_class_id', $this->getId());
    }

    /**
     * Get Customer Groups Data Objects with this tax class
     *
     * @return CustomerGroup[]
     */
    public function getAssignedDataObjects()
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            $this->filterBuilder->setField('tax_class_id')->setValue($this->getId())->create()
        )->create();
        $result = $this->groupService->searchGroups($searchCriteria);
        return $result->getItems();
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
