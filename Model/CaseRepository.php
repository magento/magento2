<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Api\Data\CaseInterfaceFactory;
use Magento\Signifyd\Api\Data\CaseSearchResultsInterface;
use Magento\Signifyd\Api\Data\CaseSearchResultsInterfaceFactory;
use Magento\Signifyd\Model\ResourceModel\CaseEntity as CaseResourceModel;
use Magento\Signifyd\Model\ResourceModel\CaseEntity\Collection;
use Magento\Signifyd\Model\ResourceModel\CaseEntity\CollectionFactory;

/**
 * Repository for Case interface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CaseRepository implements CaseRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CaseSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CaseInterfaceFactory
     */
    private $caseFactory;

    /**
     * @var CaseResourceModel
     */
    private $resourceModel;

    /**
     * CaseRepository constructor.
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $collectionFactory
     * @param CaseSearchResultsInterfaceFactory $searchResultsFactory
     * @param CaseInterfaceFactory $caseFactory
     * @param CaseResourceModel $resourceModel
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $collectionFactory,
        CaseSearchResultsInterfaceFactory $searchResultsFactory,
        CaseInterfaceFactory $caseFactory,
        CaseResourceModel $resourceModel
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->caseFactory = $caseFactory;
        $this->resourceModel = $resourceModel;
    }

    /**
     * @inheritdoc
     */
    public function save(CaseInterface $case)
    {
        /** @var CaseEntity $case */
        $this->resourceModel->save($case);

        return $case;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        /** @var CaseEntity $case */
        $case = $this->caseFactory->create();
        $this->resourceModel->load($case, $id);

        return $case;
    }

    /**
     * @inheritdoc
     */
    public function getByCaseId($caseId)
    {
        /** @var CaseEntity $case */
        $case = $this->caseFactory->create();
        $this->resourceModel->load($case, $caseId, 'case_id');

        return $case->getEntityId() ? $case : null;
    }

    /**
     * @inheritdoc
     */
    public function delete(CaseInterface $case)
    {
        $this->resourceModel->delete($case);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteria $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var CaseSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
