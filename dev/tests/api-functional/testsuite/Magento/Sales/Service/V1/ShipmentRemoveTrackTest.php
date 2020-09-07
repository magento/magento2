<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ShipmentRemoveTrackTest
 */
class ShipmentRemoveTrackTest extends WebapiAbstract
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
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test shipment remove track service
     *
     * @magentoApiDataFixture Magento/Sales/_files/shipment.php
     */
    public function testShipmentRemoveTrack()
    {
        $shipmentCollection = $this->objectManager->get(Collection::class);
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $shipmentCollection->getFirstItem();

        $trackEntity = $this->objectManager->get(TrackFactory::class)
            ->create(
                [
                    'data' => [
                        ShipmentTrackInterface::ENTITY_ID => null,
                        ShipmentTrackInterface::ORDER_ID => $shipment->getOrderId(),
                        ShipmentTrackInterface::PARENT_ID => $shipment->getId(),
                        ShipmentTrackInterface::WEIGHT => 20,
                        ShipmentTrackInterface::QTY => 5,
                        ShipmentTrackInterface::TRACK_NUMBER => 2,
                        ShipmentTrackInterface::DESCRIPTION => 'Shipment description',
                        ShipmentTrackInterface::TITLE => 'Shipment title',
                        ShipmentTrackInterface::CARRIER_CODE => Track::CUSTOM_CARRIER_CODE,
                    ]
                ]
            );

        /** @var ShipmentTrackInterface $trackEntity */
        $trackEntity = $this->objectManager->get(ShipmentTrackRepositoryInterface::class)
            ->save($trackEntity);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/shipment/track/' . $trackEntity->getEntityId(),
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'deleteById',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, ['id' => $trackEntity->getEntityId()]);

        self::assertTrue($result);
        $this->assertNoAvailableTrackItems($shipment->getId());
    }

    /**
     * Performs assertion for provided shipment.
     *
     * @param int $shipmentId
     * @return void
     */
    private function assertNoAvailableTrackItems($shipmentId)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter(ShipmentTrackInterface::PARENT_ID, $shipmentId)
            ->create();

        $items = $this->objectManager->get(ShipmentTrackRepositoryInterface::class)
            ->getList($searchCriteria)
            ->getItems();

        self::assertEmpty($items);
    }
}
