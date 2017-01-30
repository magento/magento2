<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

class LayoutProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Cart\LayoutProcessor
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $merger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionCollection;

    protected function setUp()
    {
        $this->merger = $this->getMock('\Magento\Checkout\Block\Checkout\AttributeMerger', [], [], '', false);
        $this->countryCollection = $this->getMock(
            '\Magento\Directory\Model\ResourceModel\Country\Collection',
            [],
            [],
            '',
            false
        );
        $this->regionCollection = $this->getMock(
            '\Magento\Directory\Model\ResourceModel\Region\Collection',
            [],
            [],
            '',
            false
        );

        $this->model = new \Magento\Checkout\Block\Cart\LayoutProcessor(
            $this->merger,
            $this->countryCollection,
            $this->regionCollection
        );
    }

    public function testProcess()
    {
        $countries = [];
        $regions = [];

        $layout = [];
        $layout['components']['block-summary']['children']['block-shipping']['children']
        ['address-fieldsets']['children'] = [
            'fieldOne' => ['param' => 'value'],
            'fieldTwo' => ['param' => 'value']
        ];
        $layoutPointer = &$layout['components']['block-summary']['children']['block-shipping']
        ['children']['address-fieldsets']['children'];

        $this->countryCollection->expects($this->once())->method('load')->willReturnSelf();
        $this->countryCollection->expects($this->once())->method('toOptionArray')->willReturn($countries);

        $this->regionCollection->expects($this->once())->method('load')->willReturnSelf();
        $this->regionCollection->expects($this->once())->method('toOptionArray')->willReturn($regions);

        $layoutMerged = $layout;
        $layoutMerged['components']['block-summary']['children']['block-shipping']['children']
        ['address-fieldsets']['children']['fieldThree'] = ['param' => 'value'];
        $layoutMergedPointer = &$layoutMerged['components']['block-summary']['children']['block-shipping']
        ['children']['address-fieldsets']['children'];

        $elements = [
            'city' => [
                'visible' => false,
                'formElement' => 'input',
                'label' => __('City'),
                'value' => null
            ],
            'country_id' => [
                'visible' => 1,
                'formElement' => 'select',
                'label' => __('Country'),
                'options' => [],
                'value' => null
            ],
            'region_id' => [
                'visible' => 1,
                'formElement' => 'select',
                'label' => __('State/Province'),
                'options' => [],
                'value' => null
            ],
            'postcode' => [
                'visible' => 1,
                'formElement' => 'input',
                'label' => __('Zip/Postal Code'),
                'value' => null
            ]
        ];

        $this->merger->expects($this->once())
            ->method('merge')
            ->with($elements, 'checkoutProvider', 'shippingAddress', $layoutPointer)
            ->willReturn($layoutMergedPointer);

        $this->assertEquals($layoutMerged, $this->model->process($layout));
    }
}
