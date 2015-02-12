<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Recommendations;

use Magento\AdvancedSearch\Model\Resource\RecommendationsFactory;
use Magento\Framework\Event\Observer as EventObserver;

class Observer
{

    /**
     * @var RecommendationsFactory
     */
    private $recommendationsFactory;

    /**
     * @param RecommendationsFactory $recommendationsFactory
     */
    public function __construct(RecommendationsFactory $recommendationsFactory)
    {
        $this->recommendationsFactory = $recommendationsFactory;
    }

    /**
     * Save search query relations after save search query
     *
     * @param EventObserver $observer
     * @return void
     */
    public function searchQueryEditFormAfterSave(EventObserver $observer)
    {
        $searchQueryModel = $observer->getEvent()->getDataObject();
        $queryId = $searchQueryModel->getId();
        $relatedQueries = $searchQueryModel->getSelectedQueriesGrid();

        if (strlen($relatedQueries) == 0) {
            $relatedQueries = [];
        } else {
            $relatedQueries = explode('&', $relatedQueries);
        }

        $this->recommendationsFactory->create()->saveRelatedQueries($queryId, $relatedQueries);
    }
}
