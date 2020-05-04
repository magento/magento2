<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Usps\Test\Unit\Model\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Usps\Model\Carrier;
use Magento\Usps\Model\Source\Generic;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    /**
     * @var Generic
     */
    protected $_generic;

    /**
     * @var Carrier
     */
    protected $_uspsModel;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->_uspsModel = $this->getMockBuilder(
            Carrier::class
        )->setMethods(
            ['getCode']
        )->disableOriginalConstructor()
            ->getMock();

        $this->_generic = $helper->getObject(
            Generic::class,
            ['shippingUsps' => $this->_uspsModel]
        );
    }

    /**
     * @dataProvider getCodeDataProvider
     * @param array$expected array
     * @param array $options
     */
    public function testToOptionArray($expected, $options)
    {
        $this->_uspsModel->expects($this->any())->method('getCode')->willReturn($options);

        $this->assertEquals($expected, $this->_generic->toOptionArray());
    }

    /**
     * @return array expected result and return of \Magento\Usps\Model\Carrier::getCode
     */
    public function getCodeDataProvider()
    {
        return [
            [[['value' => 'Val', 'label' => 'Label']], ['Val' => 'Label']],
            [[], false]
        ];
    }
}
