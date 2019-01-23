<?php

declare(strict_types=1);

namespace Chizhov\Status\Model\CustomerStatus\Command;

use Chizhov\Status\Api\Data\CustomerStatusSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface GetListInterface
{
    /**
     * Find customer statuses by given SearchCriteria
     * SearchCriteria is not required because load all stocks is useful case.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Chizhov\Status\Api\Data\CustomerStatusSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria = null): CustomerStatusSearchResultsInterface;
}
