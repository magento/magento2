<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

use Magento\Framework\DataObject;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;

/**
 * Contains list of tests for Shipping model
 */
class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Shipping
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(Shipping::class);
    }

    /**
     * Checks shipping rates processing by address.
     * @covers \Magento\Shipping\Model\Shipping::collectRatesByAddress
     * @magentoConfigFixture carriers/flatrate/active 1
     * @magentoConfigFixture carriers/flatrate/price 5.00
     */
    public function testCollectRatesByAddress()
    {
        $address = $this->objectManager->create(DataObject::class, [
            'data' => [
                'region_id' => 'CA',
                'postcode' => '11111',
                'lastname' => 'John',
                'firstname' => 'Doe',
                'street' => 'Some street',
                'city' => 'Los Angeles',
                'email' => 'john.doe@example.com',
                'telephone' => '11111111',
                'country_id' => 'US',
                'item_qty' => 1
            ]
        ]);
        /** @var Shipping $result */
        $result = $this->model->collectRatesByAddress($address, 'flatrate');

        static::assertEquals($this->model, $result);

        $rates = $result->getResult()->getAllRates();
        static::assertNotEmpty($rates);

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $flatRate */
        $flatRate = array_pop($rates);
        static::assertEquals(5, $flatRate->getData('price'));
    }
}
