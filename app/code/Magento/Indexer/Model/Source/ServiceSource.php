<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Source;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Indexer\Model\Source\ServiceSource
 *
 * @since 2.0.0
 */
class ServiceSource implements DataInterface
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @var string
     * @since 2.0.0
     */
    private $service;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.0.0
     */
    private $searchCriteriaBuilder;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string $service
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function getRequestedFields(SearchResults $list, array $fields)
    {
        $requestedData = [];
        foreach ($list->getItems() as $key => $item) {
            foreach (array_keys($fields) as $fieldName) {
                if (!isset($item[$fieldName])) {
                    throw new NotFoundException(__("Field '%1' not found", $fieldName));
                }

                $requestedData[$key][$fieldName] = $item[$fieldName];
            }
        }
        return $requestedData;
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    private function getService()
    {
        return $this->objectManager->get($this->service);
    }
}
