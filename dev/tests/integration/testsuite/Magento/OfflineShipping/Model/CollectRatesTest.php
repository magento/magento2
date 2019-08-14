<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Model;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Shipping;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Integration tests for offline shipping carriers.
 * @magentoAppIsolation enabled
 */
class CollectRatesTest extends \PHPUnit\Framework\TestCase
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
    protected $carrier = 'flatrate';

    /**
     * @var string
     */
    protected $errorMessage = 'This shipping method is not available. To use this shipping method, please contact us.';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->shipping = $this->objectManager->get(Shipping::class);
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/flatrate/sallowspecific 1
     * @magentoConfigFixture default_store carriers/flatrate/specificcountry UK
     * @magentoConfigFixture default_store carriers/flatrate/showmethod 1
     */
    public function testCollectRatesWhenShippingCarrierIsAvailableAndNotApplicable()
    {
        $result = $this->shipping->collectRatesByAddress($this->getAddress(), $this->carrier);
        $rate = $this->getRate($result->getResult());

        static::assertEquals($this->carrier, $rate->getData('carrier'));
        static::assertEquals($this->errorMessage, $rate->getData('error_message'));
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 0
     * @magentoConfigFixture default_store carriers/flatrate/sallowspecific 1
     * @magentoConfigFixture default_store carriers/flatrate/specificcountry UK
     * @magentoConfigFixture default_store carriers/flatrate/showmethod 1
     */
    public function testCollectRatesWhenShippingCarrierIsNotAvailableAndNotApplicable()
    {
        $result = $this->shipping->collectRatesByAddress($this->getAddress(), $this->carrier);
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
