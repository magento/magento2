<?php
/**
 * Customer group collection
 *
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
namespace Magento\Customer\Model\Resource\Group\Grid;

use Magento\Customer\Service\V1\Dto\CustomerGroup;
use Magento\Customer\Service\V1\Dto\Filter;
use Magento\Customer\Service\V1\Dto\SearchCriteria;

class ServiceCollection extends \Magento\Data\Collection
{
    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface
     */
    protected $groupService;

    /**
     * @var \Magento\Customer\Service\V1\Dto\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Customer\Service\V1\Dto\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService
     * @param \Magento\Customer\Service\V1\Dto\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Service\V1\Dto\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService,
        \Magento\Customer\Service\V1\Dto\FilterBuilder $filterBuilder,
        \Magento\Customer\Service\V1\Dto\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($entityFactory);
        $this->groupService = $groupService;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Load customer group collection data from service
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return  \Magento\Data\Collection
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $searchCriteria = $this->getSearchCriteria();
            $searchResults = $this->groupService->searchGroups($searchCriteria);
            $this->_totalRecords = $searchResults->getTotalCount();
            /** @var CustomerGroup[] $groups */
            $groups = $searchResults->getItems();
            foreach ($groups as $group) {
                $groupItem = new \Magento\Object();
                $groupItem->addData($group->__toArray());
                $this->_addItem($groupItem);
            }
            $this->_setIsLoaded();
        }
        return $this;
    }

    public function addFieldToFilter($field, $condition)
    {
        // TODO this is broken until the Widget/Grid can be re-written not to have db logic in it
        return $this;
    }

    protected function getSearchCriteria()
    {
        foreach ($this->_filters as $filter) {
            $this->filerBuilder->setField($filter['field'])
                ->setValue($filter['value'])
                ->setConditionType($filter['type']);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        }
        foreach ($this->_orders as $field => $direction) {
            $this->searchCriteriaBuilder->addSortOrder(
                $field, $direction == 'ASC' ? SearchCriteria::SORT_ASC : SearchCriteria::SORT_DESC);
        }
        $this->searchCriteriaBuilder->setCurrentPage($this->_curPage);
        $this->searchCriteriaBuilder->setPageSize($this->_pageSize);
        return $this->searchCriteriaBuilder->create();
    }
}
