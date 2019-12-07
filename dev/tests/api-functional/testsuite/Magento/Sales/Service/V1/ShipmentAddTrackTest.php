<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ShipmentAddTrackTest
 */
class ShipmentAddTrackTest extends WebapiAbstract
{
    /**
     * Read name of service
     */
    const SERVICE_READ_NAME = 'salesShipmentTrackRepositoryV1';

    /**
     * Version of service
     */
    const SERVICE_VERSION = 'V1';

    /**
     * Increment id for shipment
     */
    const SHIPMENT_INCREMENT_ID = '100000001';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Creates shipment track item.
     *
     * @magentoApiDataFixture Magento/Sales/_files/shipment.php
     */
    public function testShipmentAddTrack()
    {
        $shipmentCollection = $this->objectManager->get(Collection::class);
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $shipmentCollection->getFirstItem();

        $trackData = [
            ShipmentTrackInterface::ENTITY_ID => null,
            ShipmentTrackInterface::ORDER_ID => $shipment->getOrderId(),
            ShipmentTrackInterface::PARENT_ID => $shipment->getId(),
            ShipmentTrackInterface::WEIGHT => 20,
            ShipmentTrackInterface::QTY => 5,
            ShipmentTrackInterface::TRACK_NUMBER => 2,
            ShipmentTrackInterface::DESCRIPTION => 'Shipment description',
            ShipmentTrackInterface::TITLE => 'Shipment title',
            ShipmentTrackInterface::CARRIER_CODE => Track::CUSTOM_CARRIER_CODE,
        ];

        $result = $this->_webApiCall($this->getServiceInfo(), ['entity' => $trackData]);

        self::assertNotEmpty($result);
        self::assertNotEmpty($result[ShipmentTrackInterface::ENTITY_ID]);
        self::assertEquals($shipment->getId(), $result[ShipmentTrackInterface::PARENT_ID]);
    }

    /**
     * Shipment Tracking throw an error if order doesn't exist.
     *
     * @magentoApiDataFixture Magento/Sales/_files/shipment.php
     * @magentoApiDataFixture Magento/Sales/_files/order_list.php
     */
    public function testShipmentTrackWithFailedOrderId()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $orderCollection = $this->objectManager->get(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $order = $orderCollection->getLastItem();
        // Order ID from Magento/Sales/_files/order_list.php
        $failedOrderId = $order->getId();
        $shipmentCollection = $this->objectManager->get(Collection::class);
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $shipmentCollection->getFirstItem();
        $trackData = [
            ShipmentTrackInterface::ENTITY_ID => null,
            ShipmentTrackInterface::ORDER_ID => $failedOrderId,
            ShipmentTrackInterface::PARENT_ID => $shipment->getId(),
            ShipmentTrackInterface::WEIGHT => 20,
            ShipmentTrackInterface::QTY => 5,
            ShipmentTrackInterface::TRACK_NUMBER => 2,
            ShipmentTrackInterface::DESCRIPTION => 'Shipment description',
            ShipmentTrackInterface::TITLE => 'Shipment title',
            ShipmentTrackInterface::CARRIER_CODE => Track::CUSTOM_CARRIER_CODE,
        ];
        $exceptionMessage = '';

        try {
            $this->_webApiCall($this->getServiceInfo(), ['entity' => $trackData]);
        } catch (\SoapFault $e) {
            $exceptionMessage = $e->getMessage();
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $exceptionMessage = $errorObj['message'];
        }

        $this->assertContains(
            $exceptionMessage,
            'Could not save the shipment tracking.',
            'SoapFault or CouldNotSaveException does not contain exception message.'
        );
    }

    /**
     * Returns details about API endpoints and services.
     *
     * @return array
     */
    private function getServiceInfo()
    {
        return [
            'rest' => [
                'resourcePath' => '/V1/shipment/track',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'save',
            ],
        ];
    }
}
