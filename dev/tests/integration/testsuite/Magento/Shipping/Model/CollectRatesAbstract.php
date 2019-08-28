<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Model;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\Result;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Abstract class for testing shipping carriers.
 */
abstract class CollectRatesAbstract extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Shipping
     */
    protected $shipping;

    /**
     * @var string
     */
    protected $carrier = '';

    /**
     * @var string
     */
    protected $errorMessage = '';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->shipping = $this->objectManager->get(Shipping::class);
    }

    /**
     * Tests that an error message is displayed when the shipping method is enabled and not applicable.
     *
     * @return void
     */
    public function testCollectRatesWhenShippingCarrierIsAvailableAndNotApplicable()
    {
        $result = $this->shipping->collectRatesByAddress($this->getAddress(), $this->carrier);
        $rate = $this->getRate($result->getResult());

        static::assertEquals($this->carrier, $rate->getData('carrier'));
        static::assertEquals($this->errorMessage, $rate->getData('error_message'));
    }

    /**
     * Tests that shipping rates don't return when the shipping method is disabled and not applicable.
     *
     * @return void
     */
    public function testCollectRatesWhenShippingCarrierIsNotAvailableAndNotApplicable()
    {
        $result = $this->shipping->collectRatesByAddress($this->getAddress(), $this->carrier);
        $rate = $this->getRate($result->getResult());

        static::assertNull($rate);
    }

    /**
     * Returns customer address.
     *
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
     * Returns shipping rate by the result.
     *
     * @param Result $result
     * @return Method|Error
     */
    private function getRate(Result $result)
    {
        $rates = $result->getAllRates();

        return array_pop($rates);
    }
}
