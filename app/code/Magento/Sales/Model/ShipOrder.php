<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\OrderValidatorInterface;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Model\Order\Shipment\NotifierInterface;
use Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;
use Magento\Sales\Model\Order\Shipment\Validation\TrackValidator;
use Magento\Sales\Model\Order\Validation\CanShip;
use Magento\Sales\Model\Order\Shipment\OrderRegistrarInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ShipOrder
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipOrder implements ShipOrderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ShipmentDocumentFactory
     */
    private $shipmentDocumentFactory;

    /**
     * @var ShipmentValidatorInterface
     */
    private $shipmentValidator;

    /**
     * @var OrderStateResolverInterface
     */
    private $orderStateResolver;

    /**
     * @var OrderConfig
     */
    private $config;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var NotifierInterface
     */
    private $notifierInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderValidatorInterface
     */
    private $orderValidator;

    /**
     * @var OrderRegistrarInterface
     */
    private $orderRegistrar;

    /**
     * @param ResourceConnection $resourceConnection
     * @param OrderRepositoryInterface $orderRepository
     * @param ShipmentDocumentFactory $shipmentDocumentFactory
     * @param ShipmentValidatorInterface $shipmentValidator
     * @param OrderValidatorInterface $orderValidator
     * @param OrderStateResolverInterface $orderStateResolver
     * @param OrderConfig $config
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param NotifierInterface $notifierInterface
     * @param OrderRegistrarInterface $orderRegistrar
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $orderRepository,
        ShipmentDocumentFactory $shipmentDocumentFactory,
        ShipmentValidatorInterface $shipmentValidator,
        OrderValidatorInterface $orderValidator,
        OrderStateResolverInterface $orderStateResolver,
        OrderConfig $config,
        ShipmentRepositoryInterface $shipmentRepository,
        NotifierInterface $notifierInterface,
        OrderRegistrarInterface $orderRegistrar,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->shipmentValidator = $shipmentValidator;
        $this->orderValidator = $orderValidator;
        $this->orderStateResolver = $orderStateResolver;
        $this->config = $config;
        $this->shipmentRepository = $shipmentRepository;
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
        $orderValidationResult = $this->orderValidator->validate(
            $order,
            [
                CanShip::class
            ]
        );
        $shipmentValidationResult = $this->shipmentValidator->validate(
            $shipment,
            [
                QuantityValidator::class,
                TrackValidator::class
            ]
        );
        $validationMessages = array_merge($orderValidationResult, $shipmentValidationResult);
        if (!empty($validationMessages)) {
            throw new \Magento\Sales\Exception\DocumentValidationException(
                __("Shipment Document Validation Error(s):\n" . implode("\n", $validationMessages))
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
