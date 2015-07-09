<?php
/**
 * Created by PhpStorm.
 * User: akaplya
 * Date: 08.07.15
 * Time: 18:58
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

/**
 * Class Reporting
 */
class Reporting implements ReportingInterface
{
    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var DocumentFactory
     */
    protected $documentFactory;

    /**
     * @var
     */
    protected $fulltextFilter;

    /**
     * @var
     */
    protected $regularFilter;
    /**
     * @var array
     */
    protected $collections;

    /**
     * @param array $collections
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        FulltextFilter $fulltextFilter,
        RegularFilter $regularFilter,
        DocumentFactory $documentFactory,
        array $collections
    ) {
        $this->regularFilter = $regularFilter;
        $this->fulltextFilter = $fulltextFilter;
        $this->searchResultFactory = $searchResultFactory;
        $this->collections = $collections;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        if ( isset($this->collections[$searchCriteria->getRequestName()])
            && $this->collections[$searchCriteria->getRequestName()] instanceof AbstractCollection
        ) {
            /** @var AbstractCollection $collection */
            $collection = $this->collections[$searchCriteria->getRequestName()];
            if ($searchCriteria->getSearchTerm()) {
                $this->filterPool->registerNewFilter($searchCriteria->getSearchTerm(), null, 'fulltext');
            }
            /** @var \Magento\Framework\Api\Search\FilterGroup $group */
            foreach ($searchCriteria->getFilterGroups() as $group) {
                foreach ($group->getFilters() as $filter) {
                    $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                    $this->filterPool->registerNewFilter(
                        [$condition => $filter->getValue()],
                        $filter->getField(),
                        'regular'
                    );
                }
            }
            $this->filterPool->applyFilters($this->collections[$searchCriteria->getRequestName()]);
            /** @var SearchResultInterface $searchResult */
            $searchResult = $this->searchResultFactory->create();
            $items = [];
            /** @var \Magento\Framework\Model\AbstractModel $item */
            foreach ($collection as $item) {
                $items[$item->getId()] = $item->getData();
            }
            $searchResult->setItems($items);
            $searchResult->setSearchCriteria($searchCriteria);
            $searchResult->setTotalCount($collection->getSize());
            return $searchResult;

        }
    }
}