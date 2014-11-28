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
namespace Magento\Customer\Model\Resource\Group\Grid;

use Magento\Core\Model\EntityFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Api\AbstractServiceCollection;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SimpleDataObjectConverter;

/**
 * Customer group collection backed by services
 */
class ServiceCollection extends AbstractServiceCollection
{
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SimpleDataObjectConverter
     */
    protected $simpleDataObjectConverter;

    /**
     * @param EntityFactory $entityFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param SimpleDataObjectConverter $simpleDataObjectConverter
     */
    public function __construct(
        EntityFactory $entityFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        GroupRepositoryInterface $groupRepository,
        SimpleDataObjectConverter $simpleDataObjectConverter
    ) {
        parent::__construct($entityFactory, $filterBuilder, $searchCriteriaBuilder, $sortOrderBuilder);
        $this->groupRepository = $groupRepository;
        $this->simpleDataObjectConverter = $simpleDataObjectConverter;
    }

    /**
     * Load customer group collection data from service
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $searchCriteria = $this->getSearchCriteria();
            $searchResults = $this->groupRepository->getList($searchCriteria);
            $this->_totalRecords = $searchResults->getTotalCount();
            /** @var GroupInterface[] $groups */
            $groups = $searchResults->getItems();
            foreach ($groups as $group) {
                $groupItem = new \Magento\Framework\Object();
                $groupItem->addData($this->simpleDataObjectConverter->toFlatArray($group));
                $this->_addItem($groupItem);
            }
            $this->_setIsLoaded();
        }
        return $this;
    }
}
