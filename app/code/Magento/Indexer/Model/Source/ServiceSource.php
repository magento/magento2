<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Source;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;

class ServiceSource implements DataInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $service;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string $service
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        $service
    ) {
        $this->objectManager = $objectManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(array $fieldsData)
    {
        $service = $this->getService();

        $searchCriteria = $this->searchCriteriaBuilder->create();

        /** @var SearchResults $list */
        $list = $service->getList($searchCriteria);

        return $this->getRequestedFields($list, $fieldsData);
    }

    /**
     * @param SearchResults $list
     * @param array $fields
     * @return array
     * @throws NotFoundException
     */
    private function getRequestedFields(SearchResults $list, array $fields)
    {
        $requestedData = [];
        foreach ($list->getItems() as $key => $item) {
            foreach (array_keys($fields) as $fieldName) {
                if (!isset($item[$fieldName])) {
                    throw new NotFoundException(__("Field {$fieldName} not found"));
                }

                $requestedData[$key][$fieldName] = $item[$fieldName];
            }
        }
        return $requestedData;
    }

    /**
     * @return mixed
     */
    private function getService()
    {
        return $this->objectManager->get($this->service);
    }
}
