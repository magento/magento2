<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Usps\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Usps\Helper\Data;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $_helperData;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $arguments = [
            'context' => $this->createMock(Context::class),
        ];

        $this->_helperData = $helper->getObject(Data::class, $arguments);
    }

    /**
     * @covers \Magento\Usps\Helper\Data::displayGirthValue
     * @dataProvider shippingMethodDataProvider
     */
    public function testDisplayGirthValue($shippingMethod)
    {
        $this->assertTrue($this->_helperData->displayGirthValue($shippingMethod));
    }

    /**
     * @covers \Magento\Usps\Helper\Data::displayGirthValue
     */
    public function testDisplayGirthValueFalse()
    {
        $this->assertFalse($this->_helperData->displayGirthValue('test_shipping_method'));
    }

    /**
     * @return array shipping method name
     */
    public static function shippingMethodDataProvider()
    {
        return [
            ['usps_0_FCLE'],   // First-Class Mail Large Envelope
            ['usps_1'],        // Priority Mail
            ['usps_2'],        // Priority Mail Express Hold For Pickup
            ['usps_3'],        // Priority Mail Express
            ['usps_4'],        // Retail Ground
            ['usps_6'],        // Media Mail
            ['usps_INT_1'],    // Priority Mail Express International
            ['usps_INT_2'],    // Priority Mail International
            ['usps_INT_4'],    // Global Express Guaranteed (GXG)
            ['usps_INT_7'],    // Global Express Guaranteed Non-Document Non-Rectangular
            ['usps_INT_8'],    // Priority Mail International Flat Rate Envelope
            ['usps_INT_9'],    // Priority Mail International Medium Flat Rate Box
            ['usps_INT_10'],   // Priority Mail Express International Flat Rate Envelope
            ['usps_INT_11'],   // Priority Mail International Large Flat Rate Box
            ['usps_INT_12'],   // USPS GXG Envelopes
            ['usps_INT_14'],   // First-Class Mail International Large Envelope
            ['usps_INT_16'],   // Priority Mail International Small Flat Rate Box
            ['usps_INT_20'],   // Priority Mail International Small Flat Rate Envelope
            ['1058'],          // Ground Advantage™
            ['4058'],          // Ground Advantage™ HAZMAT
            ['6058'],          // Ground Advantage™ Parcel locker
            ['2058'],          // Ground Advantage™ Hold for pickup
            ['4096'],          // Ground Advantage™ Cubic HAZMAT
            ['1096'],          // Ground Advantage™ Cubic
            ['2096'],          // Ground Advantage™ Cubic Hold for pickup
            ['6096'],          // Ground Advantage™ Cubic Parcel locker
        ];
    }
}
