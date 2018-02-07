<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ShipmentLabelGetTest
 */
class ShipmentLabelGetTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/shipment';
    const SERVICE_READ_NAME = 'salesShipmentManagementV1';
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
        $shipmentCollection = $this->objectManager->get('Magento\Sales\Model\ResourceModel\Order\Shipment\Collection');
        $shipment = $shipmentCollection->getFirstItem();
        $shipment->setShippingLabel('test_shipping_label');
        $shipment->save();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $shipment->getId() . '/label',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'getLabel',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['id' => $shipment->getId()]);
        $this->assertEquals($result, 'test_shipping_label');
    }
}
