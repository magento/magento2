<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
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
 * @since 2.2.0
 */
class CaseRepository implements CaseRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     * @since 2.2.0
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     * @since 2.2.0
     */
    private $collectionFactory;

    /**
     * @var CaseSearchResultsInterfaceFactory
     * @since 2.2.0
     */
    private $searchResultsFactory;

    /**
     * @var CaseInterfaceFactory
     * @since 2.2.0
     */
    private $caseFactory;

    /**
     * @var CaseResourceModel
     * @since 2.2.0
     */
    private $resourceModel;

    /**
     * CaseRepository constructor.
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $collectionFactory
     * @param CaseSearchResultsInterfaceFactory $searchResultsFactory
     * @param CaseInterfaceFactory $caseFactory
     * @param CaseResourceModel $resourceModel
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function save(CaseInterface $case)
    {
        /** @var CaseEntity $case */
        $this->resourceModel->save($case);

        return $case;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function delete(CaseInterface $case)
    {
        $this->resourceModel->delete($case);

        return true;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
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
