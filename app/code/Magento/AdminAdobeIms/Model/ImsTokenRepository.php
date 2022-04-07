<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Exception;
use Magento\AdminAdobeIms\Model\ResourceModel\ImsToken\Collection;
use Magento\AdminAdobeIms\Model\ResourceModel\ImsToken\CollectionFactory;
use Magento\AdminAdobeIms\Api\Data\ImsTokenInterface;
use Magento\AdminAdobeIms\Api\Data\ImsTokenInterfaceFactory;
use Magento\AdminAdobeIms\Api\ImsTokenRepositoryInterface;
use Magento\AdminAdobeIms\Api\Data\ImsTokenSearchResultsInterface;
use Magento\AdminAdobeIms\Api\Data\ImsTokenSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Represent user profile repository
 */
class ImsTokenRepository implements ImsTokenRepositoryInterface
{
    private const ADMIN_USER_ID = 'admin_user_id';

    /**
     * @var ResourceModel\ImsToken
     */
    private $resource;

    /**
     * @var ImsTokenInterfaceFactory
     */
    private $entityFactory;

    /**
     * @var array
     */
    private $loadedEntities = [];

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $entityCollectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;
    /**
     * @var ImsTokenSearchResultsInterfaceFactory
     */
    private ImsTokenSearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * UserProfileRepository constructor.
     *
     * @param ResourceModel\ImsToken $resource
     * @param ImsTokenInterfaceFactory $entityFactory
     * @param LoggerInterface $logger
     * @param CollectionFactory $entityCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ImsTokenSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel\ImsToken $resource,
        ImsTokenInterfaceFactory $entityFactory,
        LoggerInterface $logger,
        CollectionFactory $entityCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ImsTokenSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(ImsTokenInterface $entity): void
    {
        try {
            $this->resource->save($entity);
            $this->loadedEntities[$entity->getId()] = $entity;
        } catch (Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(__('Could not save ims token.'), $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function get(int $entityId): ImsTokenInterface
    {
        if (isset($this->loadedEntities[$entityId])) {
            return $this->loadedEntities[$entityId];
        }

        $entity = $this->entityFactory->create();
        $this->resource->load($entity, $entityId);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Could not find ims token id: %id.', ['id' => $entityId]));
        }

        return $this->loadedEntities[$entity->getId()] = $entity;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ImsTokenSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->entityCollectionFactory->create();

        /** @var  $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $this->collectionProcessor->process($searchCriteria, $collection);

        if ($searchCriteria->getPageSize()) {
            $searchResults->setTotalCount($collection->getSize());
        } else {
            $searchResults->setTotalCount(count($collection));
        }

        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function deleteByUserId(int $id): bool
    {
      /*  $entity = $this->entityFactory->create()->load($id);

        if (!$entity->getId()) {
            throw new NoSuchEntityException(__(
                'Cannot delete ims token with id %1',
                $id
            ));
        }

        $this->resource->delete($entity);

        return true;*/
    }
}
