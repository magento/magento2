<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\ShipmentCommentInterface;
use Magento\Sales\Api\Data\ShipmentCommentInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentCommentSearchResultInterfaceFactory;
use Magento\Sales\Api\ShipmentCommentRepositoryInterface;
use Magento\Sales\Model\Spi\ShipmentCommentResourceInterface;
use Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CommentRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommentRepository implements ShipmentCommentRepositoryInterface
{
    /**
     * @var ShipmentCommentResourceInterface
     */
    private $commentResource;

    /**
     * @var ShipmentCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var ShipmentCommentSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ShipmentCommentSender
     */
    private $shipmentCommentSender;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ShipmentCommentResourceInterface $commentResource
     * @param ShipmentCommentInterfaceFactory $commentFactory
     * @param ShipmentCommentSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ShipmentCommentSender|null $shipmentCommentSender
     * @param ShipmentRepositoryInterface|null $shipmentRepository
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ShipmentCommentResourceInterface $commentResource,
        ShipmentCommentInterfaceFactory $commentFactory,
        ShipmentCommentSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        ShipmentCommentSender $shipmentCommentSender = null,
        ShipmentRepositoryInterface $shipmentRepository = null,
        LoggerInterface $logger = null
    ) {
        $this->commentResource = $commentResource;
        $this->commentFactory = $commentFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->shipmentCommentSender = $shipmentCommentSender
            ?: ObjectManager::getInstance()->get(ShipmentCommentSender::class);
        $this->shipmentRepository = $shipmentRepository
            ?: ObjectManager::getInstance()->get(ShipmentRepositoryInterface::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultFactory->create();
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $entity = $this->commentFactory->create();
        $this->commentResource->load($entity, $id);
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function delete(ShipmentCommentInterface $entity)
    {
        try {
            $this->commentResource->delete($entity);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the shipment comment.'), $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(ShipmentCommentInterface $entity)
    {
        try {
            $this->commentResource->save($entity);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the shipment comment.'), $e);
        }

        try {
            $shipment = $this->shipmentRepository->get($entity->getParentId());
            $this->shipmentCommentSender->send($shipment, $entity->getIsCustomerNotified(), $entity->getComment());
        } catch (\Exception $exception) {
            $this->logger->warning('Something went wrong while sending email.');
        }
        return $entity;
    }
}
