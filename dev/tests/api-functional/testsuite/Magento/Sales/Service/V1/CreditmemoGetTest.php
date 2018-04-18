<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class CreditmemoGetTest
 */
class CreditmemoGetTest extends WebapiAbstract
{
    /**
     * Resource path
     */
    const RESOURCE_PATH = '/V1/creditmemo';

    /**
     * Service read name
     */
    const SERVICE_READ_NAME = 'salesCreditmemoRepositoryV1';

    /**
     * Service version
     */
    const SERVICE_VERSION = 'V1';

    /**
     * Creditmemo id
     */
    const CREDITMEMO_INCREMENT_ID = '100000001';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Required fields are in the answer
     *
     * @var array
     */
    protected $requiredFields = [
        'entity_id',
        'store_id',
        'base_shipping_tax_amount',
        'base_discount_amount',
        'grand_total',
        'base_subtotal_incl_tax',
        'shipping_amount',
        'subtotal_incl_tax',
        'base_shipping_amount',
        'base_adjustment',
        'base_subtotal',
        'discount_amount',
        'subtotal',
        'adjustment',
        'base_grand_total',
        'base_tax_amount',
        'shipping_tax_amount',
        'tax_amount',
        'order_id',
        'state',
        'increment_id',
    ];

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test creditmemo get service
     *
     * @magentoApiDataFixture Magento/Sales/_files/creditmemo_for_get.php
     */
    public function testCreditmemoGet()
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection $creditmemoCollection */
        $creditmemoCollection =
            $this->objectManager->get('Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection');
        $creditmemo = $creditmemoCollection->getFirstItem();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $creditmemo->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'get',
            ],
        ];

        $actual = $this->_webApiCall($serviceInfo, ['id' => $creditmemo->getId()]);
        $expected = $creditmemo->getData();

        foreach ($this->requiredFields as $field) {
            $this->assertArrayHasKey($field, $actual);
            $this->assertEquals($expected[$field], $actual[$field]);
        }

        //check that nullable fields were marked as optional and were not sent
        foreach ($actual as $value) {
            $this->assertNotNull($value);
        }
    }
}
