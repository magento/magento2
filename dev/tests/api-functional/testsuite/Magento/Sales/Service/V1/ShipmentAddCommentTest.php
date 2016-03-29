<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\Sales\Api\Data\ShipmentCommentInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ShipmentAddCommentTest
 */
class ShipmentAddCommentTest extends WebapiAbstract
{
    /**
     * Service read name
     */
    const SERVICE_READ_NAME = 'salesShipmentCommentRepositoryV1';

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
     * Test shipment add comment service
     *
     * @magentoApiDataFixture Magento/Sales/_files/shipment.php
     */
    public function testShipmentAddComment()
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $shipmentCollection */
        $shipmentCollection = $this->objectManager->get('Magento\Sales\Model\ResourceModel\Order\Shipment\Collection');
        $shipment = $shipmentCollection->getFirstItem();

        $commentData = [
            ShipmentCommentInterface::COMMENT => 'Hello world!',
            ShipmentCommentInterface::ENTITY_ID => null,
            ShipmentCommentInterface::CREATED_AT => null,
            ShipmentCommentInterface::PARENT_ID => $shipment->getId(),
            ShipmentCommentInterface::IS_VISIBLE_ON_FRONT => true,
            ShipmentCommentInterface::IS_CUSTOMER_NOTIFIED => true,
        ];

        $requestData = ['entity' => $commentData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/shipment/' . $shipment->getId() . '/comments',
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
