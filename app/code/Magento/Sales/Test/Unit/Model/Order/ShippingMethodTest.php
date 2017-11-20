<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Model\Order\ShippingMethod;
use PHPUnit\Framework\TestCase;

class ShippingMethodTest extends TestCase
{
    /**
     * @var ShippingMethod
     */
    private $methodObject;

    const CARRIER_CODE = 'tablerate';
    const METHOD_CODE = 'bestway';
    const FULL_CODE = self::CARRIER_CODE . '_' . self::METHOD_CODE;

    protected function setUp()
    {
        $this->methodObject = new ShippingMethod(self::CARRIER_CODE, self::METHOD_CODE);
    }
    public function testCanBeInstantiatedFromFullMethodCode()
    {
        $this->assertEquals(
            new ShippingMethod(self::CARRIER_CODE, self::METHOD_CODE),
            ShippingMethod::fromFullShippingMethodCode(self::FULL_CODE)
        );
    }

    public function testCanBeUsedAsString()
    {
        $this->assertEquals(self::FULL_CODE, $this->methodObject);
    }

    public function testProvidesCarrierCode()
    {
        $this->assertEquals(self::CARRIER_CODE, $this->methodObject->getCarrierCode());
    }

    public function testProvidesMethodCode()
    {
        $this->assertEquals(self::METHOD_CODE, $this->methodObject->getMethod());
    }

}