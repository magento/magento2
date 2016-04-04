<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\Sales\Api\Data\ShipmentTrackInterface;
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
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test shipment add track service
     *
     * @magentoApiDataFixture Magento/Sales/_files/shipment.php
     */
    public function testShipmentAddTrack()
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipmentCollection = $this->objectManager->get('Magento\Sales\Model\ResourceModel\Order\Shipment\Collection');
        $shipment = $shipmentCollection->getFirstItem();

        $trackData = [
            ShipmentTrackInterface::ENTITY_ID => null,
            ShipmentTrackInterface::ORDER_ID => $shipment->getOrderId(),
            ShipmentTrackInterface::CREATED_AT => null,
            ShipmentTrackInterface::PARENT_ID => $shipment->getId(),
            ShipmentTrackInterface::WEIGHT => 20,
            ShipmentTrackInterface::QTY => 5,
            ShipmentTrackInterface::TRACK_NUMBER => 2,
            ShipmentTrackInterface::DESCRIPTION => 'Shipment description',
            ShipmentTrackInterface::TITLE => 'Shipment title',
            ShipmentTrackInterface::CARRIER_CODE => \Magento\Sales\Model\Order\Shipment\Track::CUSTOM_CARRIER_CODE,
            ShipmentTrackInterface::CREATED_AT => null,
            ShipmentTrackInterface::UPDATED_AT => null,
        ];

        $requestData = ['entity' => $trackData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/shipment/track',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'save',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotEmpty($result);
    }
}
