<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\Result;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Contains list of tests for Shipping model.
 * @magentoAppIsolation enabled
 */
class ShippingTest extends \PHPUnit\Framework\TestCase
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
        /** @var Shipping $result */
        $result = $this->model->collectRatesByAddress($this->getAddress(), 'flatrate');
        static::assertInstanceOf(Shipping::class, $result);

        return $result->getResult();
    }

    /**
     * Checks shipping rate details for processed address.
     * @covers \Magento\Shipping\Model\Shipping::collectRatesByAddress
     * @param Result $result
     * @depends testCollectRatesByAddress
     */
    public function testCollectRates(Result $result)
    {
        $rate = $this->getRate($result);
        static::assertInstanceOf(Method::class, $rate);
        static::assertEquals('flatrate', $rate->getData('carrier'));
        static::assertEquals(5, $rate->getData('price'));
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/flatrate/sallowspecific 1
     * @magentoConfigFixture default_store carriers/flatrate/specificcountry UK
     * @magentoConfigFixture default_store carriers/flatrate/showmethod 1
     */
    public function testShippingMethodIsActiveAndNotApplicable()
    {
        $result = $this->model->collectRatesByAddress($this->getAddress(), 'flatrate');
        $rate = $this->getRate($result->getResult());

        static::assertEquals('flatrate', $rate->getData('carrier'));
        static::assertEquals(
            'This shipping method is not available. To use this shipping method, please contact us.',
            $rate->getData('error_message')
        );
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 0
     * @magentoConfigFixture default_store carriers/flatrate/sallowspecific 1
     * @magentoConfigFixture default_store carriers/flatrate/specificcountry UK
     * @magentoConfigFixture default_store carriers/flatrate/showmethod 1
     */
    public function testShippingMethodIsNotActiveAndNotApplicable()
    {
        $result = $this->model->collectRatesByAddress($this->getAddress(), 'flatrate');
        $rate = $this->getRate($result->getResult());

        static::assertNull($rate);
    }

    /**
     * @return DataObject
     */
    private function getAddress(): DataObject
    {
        $address = $this->objectManager->create(
            DataObject::class,
            [
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
                    'item_qty' => 1,
                ],
            ]
        );

        return $address;
    }

    /**
     * @param Result $result
     * @return Method|Error
     */
    private function getRate(Result $result)
    {
        $rates = $result->getAllRates();

        return array_pop($rates);
    }
}
