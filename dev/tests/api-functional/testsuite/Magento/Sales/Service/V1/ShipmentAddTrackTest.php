<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
     * Service read name
     */
    const SERVICE_READ_NAME = 'salesShipmentTrackRepositoryV1';

    /**
     * Service version
     */
    const SERVICE_VERSION = 'V1';

    /**
     * Shipment increment id
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
     * Try to create track with wrong order ID.
     *
     * @magentoApiDataFixture Magento/Sales/_files/shipment.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Could not save the shipment tracking.
     */
    public function testShipmentAddTrackThrowsError()
    {
        $shipmentCollection = $this->objectManager->get(Collection::class);
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $shipmentCollection->getFirstItem();

        $trackData = [
            ShipmentTrackInterface::ENTITY_ID => null,
            ShipmentTrackInterface::ORDER_ID => $shipment->getOrderId() + 1,
            ShipmentTrackInterface::PARENT_ID => $shipment->getId(),
            ShipmentTrackInterface::WEIGHT => 20,
            ShipmentTrackInterface::QTY => 5,
            ShipmentTrackInterface::TRACK_NUMBER => 2,
            ShipmentTrackInterface::DESCRIPTION => 'Shipment description',
            ShipmentTrackInterface::TITLE => 'Shipment title',
            ShipmentTrackInterface::CARRIER_CODE => Track::CUSTOM_CARRIER_CODE,
        ];

        $this->_webApiCall($this->getServiceInfo(), ['entity' => $trackData]);
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
