<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ShipmentGetTest
 */
class ShipmentGetTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/shipment';
    const SERVICE_READ_NAME = 'salesShipmentRepositoryV1';
    const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/shipment.php
     */
    public function testShipmentGet()
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipmentCollection = $this->objectManager->get(
            \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection::class
        );
        $shipment = $shipmentCollection->getFirstItem();
        $shipment->load($shipment->getId());
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $shipment->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'get',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['id' => $shipment->getId()]);
        $data = $result;
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('tracks', $result);
        unset($data['items']);
        unset($data['packages']);
        unset($data['tracks']);
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                $this->assertEquals($shipment->getData($key), $value, $key);
            }
        }
        $shipmentItem = $this->objectManager->get(\Magento\Sales\Model\Order\Shipment\Item::class);
        foreach ($result['items'] as $item) {
            $shipmentItem->load($item['entity_id']);
            foreach ($item as $key => $value) {
                $this->assertEquals($shipmentItem->getData($key), $value, $key);
            }
        }
        $shipmentTrack = $this->objectManager->get(\Magento\Sales\Model\Order\Shipment\Track::class);
        foreach ($result['tracks'] as $item) {
            $shipmentTrack->load($item['entity_id']);
            foreach ($item as $key => $value) {
                $this->assertEquals($shipmentTrack->getData($key), $value, $key);
            }
        }
    }
}
