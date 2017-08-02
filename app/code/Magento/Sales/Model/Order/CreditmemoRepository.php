<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo as Resource;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\CreditmemoSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Repository class for @see \Magento\Sales\Api\Data\CreditmemoInterface
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class CreditmemoRepository implements \Magento\Sales\Api\CreditmemoRepositoryInterface
{
    /**
     * @var Metadata
     * @since 2.0.0
     */
    protected $metadata;

    /**
     * @var SearchResultFactory
     * @since 2.0.0
     */
    protected $searchResultFactory = null;

    /**
     * \Magento\Sales\Api\Data\CreditmemoInterface[]
     *
     * @var array
     * @since 2.0.0
     */
    protected $registry = [];

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     * @since 2.2.0
     */
    private $collectionProcessor;

    /**
     * CreditmemoRepository constructor.
     * @param Metadata $metadata
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @since 2.0.0
     */
    public function __construct(
        Metadata $metadata,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->metadata = $metadata;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Loads a specified credit memo.
     *
     * @param int $id The credit memo ID.
     * @return \Magento\Sales\Api\Data\CreditmemoInterface Credit memo interface.
     * @throws InputException
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('Id required'));
        }
        if (!isset($this->registry[$id])) {
            /** @var \Magento\Sales\Api\Data\CreditmemoInterface $entity */
            $entity = $this->metadata->getNewInstance()->load($id);
            if (!$entity->getEntityId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }
            $this->registry[$id] = $entity;
        }
        return $this->registry[$id];
    }

    /**
     * Create credit memo instance
     *
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     * @since 2.0.0
     */
    public function create()
    {
        return $this->metadata->getNewInstance();
    }

    /**
     * Lists credit memos that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\CreditmemoSearchResultInterface Credit memo search result interface.
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection $searchResult */
        $searchResult = $this->searchResultFactory->create();
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }

    /**
     * Deletes a specified credit memo.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $entity The credit memo.
     * @return bool
     * @throws CouldNotDeleteException
     * @since 2.0.0
     */
    public function delete(\Magento\Sales\Api\Data\CreditmemoInterface $entity)
    {
        try {
            $this->metadata->getMapper()->delete($entity);
            unset($this->registry[$entity->getEntityId()]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete credit memo'), $e);
        }
        return true;
    }

    /**
     * Performs persist operations for a specified credit memo.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $entity The credit memo.
     * @return \Magento\Sales\Api\Data\CreditmemoInterface Credit memo interface.
     * @throws CouldNotSaveException
     * @since 2.0.0
     */
    public function save(\Magento\Sales\Api\Data\CreditmemoInterface $entity)
    {
        try {
            $this->metadata->getMapper()->save($entity);
            $this->registry[$entity->getEntityId()] = $entity;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save credit memo'), $e);
        }
        return $this->registry[$entity->getEntityId()];
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 2.2.0
     * @return CollectionProcessorInterface
     * @since 2.2.0
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }
}
