<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Exception;
use Magento\AdminAdobeIms\Model\ResourceModel\ImsWebapi\Collection;
use Magento\AdminAdobeIms\Model\ResourceModel\ImsWebapi\CollectionFactory;
use Magento\AdminAdobeIms\Api\Data\ImsWebapiInterface;
use Magento\AdminAdobeIms\Api\Data\ImsWebapiInterfaceFactory;
use Magento\AdminAdobeIms\Api\ImsWebapiRepositoryInterface;
use Magento\AdminAdobeIms\Api\Data\ImsWebapiSearchResultsInterface;
use Magento\AdminAdobeIms\Api\Data\ImsWebapiSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImsWebapiRepository implements ImsWebapiRepositoryInterface
{
    private const ADMIN_USER_ID = 'admin_user_id';

    /**
     * @var ResourceModel\ImsWebapi
     */
    private ResourceModel\ImsWebapi $resource;

    /**
     * @var ImsWebapiInterfaceFactory
     */
    private ImsWebapiInterfaceFactory $entityFactory;

    /**
     * @var array
     */
    private array $loadedEntities = [];

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $entityCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @var ImsWebapiSearchResultsInterfaceFactory
     */
    private ImsWebapiSearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param ResourceModel\ImsWebapi $resource
     * @param ImsWebapiInterfaceFactory $entityFactory
     * @param LoggerInterface $logger
     * @param CollectionFactory $entityCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ImsWebapiSearchResultsInterfaceFactory $searchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ResourceModel\ImsWebapi                $resource,
        ImsWebapiInterfaceFactory              $entityFactory,
        LoggerInterface                        $logger,
        CollectionFactory                      $entityCollectionFactory,
        CollectionProcessorInterface           $collectionProcessor,
        ImsWebapiSearchResultsInterfaceFactory $searchResultsFactory,
        SearchCriteriaBuilder                  $searchCriteriaBuilder
    ) {
        $this->resource = $resource;
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function save(ImsWebapiInterface $entity): void
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
    public function get(int $entityId): ImsWebapiInterface
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
    public function getByAdminUserId(int $adminUserId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(self::ADMIN_USER_ID, $adminUserId)
            ->create();

        return $this->getList($searchCriteria)->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getByAccessTokenHash(string $tokenHash): ImsWebapiInterface
    {
        $entity = $this->entityFactory->create();
        $this->resource->load($entity, $tokenHash, 'access_token_hash');

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ImsWebapiSearchResultsInterface
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
    public function deleteByAdminUserId(int $adminUserId): bool
    {
        try {
            $entities = $this->getByAdminUserId($adminUserId);

            foreach ($entities as $entity) {
                $this->resource->delete($entity);
            }
            return true;
        } catch (Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotDeleteException(
                __('Could not delete ims tokens for admin user id %1.', $adminUserId),
                $exception
            );
        }
    }
}
