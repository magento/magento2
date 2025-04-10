<?php
/************************************************************************
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Sales\Api\Data\ShipmentCommentInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ShipmentAddCommentTest extends WebapiAbstract
{
    /**
     * Service read name
     */
    public const SERVICE_READ_NAME = 'salesShipmentCommentRepositoryV1';

    /**
     * Service version
     */
    public const SERVICE_VERSION = 'V1';

    /**
     * Shipment increment id
     */
    public const SHIPMENT_INCREMENT_ID = '100000001';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test shipment add comment service
     * @magentoApiDataFixture Magento/Sales/_files/shipment.php
     */
    public function testShipmentAddComment()
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $shipmentCollection */
        $shipmentCollection = $this->objectManager->get(
            \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection::class
        );
        $shipment = $shipmentCollection->getFirstItem();

        $commentData = [
            ShipmentCommentInterface::COMMENT => 'Hello world!',
            ShipmentCommentInterface::ENTITY_ID => null,
            ShipmentCommentInterface::CREATED_AT => null,
            ShipmentCommentInterface::PARENT_ID => $shipment->getId(),
            ShipmentCommentInterface::IS_VISIBLE_ON_FRONT => 1,
            ShipmentCommentInterface::IS_CUSTOMER_NOTIFIED => 1
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
