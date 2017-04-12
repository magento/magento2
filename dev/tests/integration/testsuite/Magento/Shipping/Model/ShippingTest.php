<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\Result;
use Magento\TestFramework\Helper\Bootstrap;

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
     * @return Result
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
        static::assertInstanceOf(Shipping::class, $result);

        return $result->getResult();
    }

    /**
     * Checks shipping rate details for processed address.
     * @covers \Magento\Shipping\Model\Shipping::collectRatesByAddress
     * @param Result $result
     * @depends testCollectRatesByAddress
     * @magentoConfigFixture carriers/flatrate/active 1
     * @magentoConfigFixture carriers/flatrate/price 5.00
     */
    public function testCollectRates(Result $result)
    {
        $rates = $result->getAllRates();
        static::assertNotEmpty($rates);

        /** @var Method $rate */
        $rate = array_pop($rates);

        static::assertInstanceOf(Method::class, $rate);
        static::assertEquals('flatrate', $rate->getData('carrier'));
        static::assertEquals(5, $rate->getData('price'));
    }
}
