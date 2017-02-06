<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ShipmentEmailTest
 */
class ShipmentEmailTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';

    const SERVICE_NAME = 'salesShipmentManagementV1';

    /**
     * @magentoApiDataFixture Magento/Sales/_files/shipment.php
     */
    public function testShipmentEmail()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $shipmentCollection = $objectManager->get(\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection::class);
        $shipment = $shipmentCollection->getFirstItem();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/shipment/' . $shipment->getId() . '/emails',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'notify',
            ],
        ];
        $requestData = ['id' => $shipment->getId()];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($result);
    }
}
