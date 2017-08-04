<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Model\Order\Shipment\NotifierInterface;
use Magento\Sales\Model\Order\Shipment\OrderRegistrarInterface;
use Magento\Sales\Model\Order\Validation\ShipOrderInterface as ShipOrderValidator;
use Psr\Log\LoggerInterface;

/**
 * Class ShipOrder
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.1.2
 */
class ShipOrder implements ShipOrderInterface
{
    /**
     * @var ResourceConnection
     * @since 2.1.2
     */
    private $resourceConnection;

    /**
     * @var OrderRepositoryInterface
     * @since 2.1.2
     */
    private $orderRepository;

    /**
     * @var ShipmentDocumentFactory
     * @since 2.1.2
     */
    private $shipmentDocumentFactory;

    /**
     * @var OrderStateResolverInterface
     * @since 2.1.2
     */
    private $orderStateResolver;

    /**
     * @var OrderConfig
     * @since 2.1.2
     */
    private $config;

    /**
     * @var ShipmentRepositoryInterface
     * @since 2.1.2
     */
    private $shipmentRepository;

    /**
     * @var ShipOrderValidator
     * @since 2.1.3
     */
    private $shipOrderValidator;

    /**
     * @var NotifierInterface
     * @since 2.1.2
     */
    private $notifierInterface;

    /**
     * @var LoggerInterface
     * @since 2.1.2
     */
    private $logger;

    /**
     * @var OrderRegistrarInterface
     * @since 2.1.2
     */
    private $orderRegistrar;

    /**
     * @param ResourceConnection $resourceConnection
     * @param OrderRepositoryInterface $orderRepository
     * @param ShipmentDocumentFactory $shipmentDocumentFactory
     * @param OrderStateResolverInterface $orderStateResolver
     * @param OrderConfig $config
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipOrderValidator $shipOrderValidator
     * @param NotifierInterface $notifierInterface
     * @param OrderRegistrarInterface $orderRegistrar
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.1.2
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $orderRepository,
        ShipmentDocumentFactory $shipmentDocumentFactory,
        OrderStateResolverInterface $orderStateResolver,
        OrderConfig $config,
        ShipmentRepositoryInterface $shipmentRepository,
        ShipOrderValidator $shipOrderValidator,
        NotifierInterface $notifierInterface,
        OrderRegistrarInterface $orderRegistrar,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->orderStateResolver = $orderStateResolver;
        $this->config = $config;
        $this->shipmentRepository = $shipmentRepository;
        $this->shipOrderValidator = $shipOrderValidator;
        $this->notifierInterface = $notifierInterface;
        $this->logger = $logger;
        $this->orderRegistrar = $orderRegistrar;
    }

    /**
     * @param int $orderId
     * @param \Magento\Sales\Api\Data\ShipmentItemCreationInterface[] $items
     * @param bool $notify
     * @param bool $appendComment
     * @param \Magento\Sales\Api\Data\ShipmentCommentCreationInterface|null $comment
     * @param \Magento\Sales\Api\Data\ShipmentTrackCreationInterface[] $tracks
     * @param \Magento\Sales\Api\Data\ShipmentPackageCreationInterface[] $packages
     * @param \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface|null $arguments
     * @return int
     * @throws \Magento\Sales\Api\Exception\DocumentValidationExceptionInterface
     * @throws \Magento\Sales\Api\Exception\CouldNotShipExceptionInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \DomainException
     * @since 2.1.2
     */
    public function execute(
        $orderId,
        array $items = [],
        $notify = false,
        $appendComment = false,
        \Magento\Sales\Api\Data\ShipmentCommentCreationInterface $comment = null,
        array $tracks = [],
        array $packages = [],
        \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface $arguments = null
    ) {
        $connection = $this->resourceConnection->getConnection('sales');
        $order = $this->orderRepository->get($orderId);
        $shipment = $this->shipmentDocumentFactory->create(
            $order,
            $items,
            $tracks,
            $comment,
            ($appendComment && $notify),
            $packages,
            $arguments
        );
        $validationMessages = $this->shipOrderValidator->validate(
            $order,
            $shipment,
            $items,
            $notify,
            $appendComment,
            $comment,
            $tracks,
            $packages
        );
        if ($validationMessages->hasMessages()) {
            throw new \Magento\Sales\Exception\DocumentValidationException(
                __("Shipment Document Validation Error(s):\n" . implode("\n", $validationMessages->getMessages()))
            );
        }
        $connection->beginTransaction();
        try {
            $this->orderRegistrar->register($order, $shipment);
            $order->setState(
                $this->orderStateResolver->getStateForOrder($order, [OrderStateResolverInterface::IN_PROGRESS])
            );
            $order->setStatus($this->config->getStateDefaultStatus($order->getState()));
            $this->shipmentRepository->save($shipment);
            $this->orderRepository->save($order);
            $connection->commit();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $connection->rollBack();
            throw new \Magento\Sales\Exception\CouldNotShipException(
                __('Could not save a shipment, see error log for details')
            );
        }
        if ($notify) {
            if (!$appendComment) {
                $comment = null;
            }
            $this->notifierInterface->notify($order, $shipment, $comment);
        }
        return $shipment->getEntityId();
    }
}
