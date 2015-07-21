<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Ui\Component\Listing\Column\Country;

class CountryTest extends \PHPUnit_Framework_TestCase
{
    /** @var Country */
    protected $component;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $uiComponentFactory;

    /** @var \Magento\Directory\Model\CountryFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $countryFactory;

    /** @var \Magento\Directory\Model\Country|\PHPUnit_Framework_MockObject_MockObject */
    protected $country;

    public function setup()
    {
        $this->context = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false
        );
        $this->uiComponentFactory = $this->getMock(
            'Magento\Framework\View\Element\UiComponentFactory',
            [],
            [],
            '',
            false
        );
        $this->countryFactory = $this->getMock(
            'Magento\Directory\Model\CountryFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->country = $this->getMock('Magento\Directory\Model\Country', [], [], '', false);
        $this->component = new Country(
            $this->context,
            $this->uiComponentFactory,
            $this->countryFactory
        );
        $this->component->setData('name', 'billing_country_id');
    }

    public function testPrepareDataSource()
    {
        $this->countryFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->country);
        $this->country->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturnSelf();
        $this->country->expects($this->once())
            ->method('getName')
            ->willReturn('Ukraine');

        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'billing_country_id' => 1
                    ],
                ]
            ]
        ];
        $expectedDataSource =  [
            'data' => [
                'items' => [
                    [
                        'billing_country_id' => 'Ukraine'
                    ],
                ]
            ]
        ];

        $this->component->prepareDataSource($dataSource);

        $this->assertEquals($expectedDataSource, $dataSource);
    }
}
