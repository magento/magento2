<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

class LayoutProcessorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Checkout\Block\Cart\LayoutProcessor
     */
    private $layoutProcessor;

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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $topDestinationCountries;

    protected function setUp()
    {
        $this->merger = $this->getMockBuilder(\Magento\Checkout\Block\Checkout\AttributeMerger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->countryCollection =
            $this->getMockBuilder(\Magento\Directory\Model\ResourceModel\Country\Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->regionCollection =
            $this->getMockBuilder(\Magento\Directory\Model\ResourceModel\Region\Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->topDestinationCountries =
            $this->getMockBuilder(\Magento\Directory\Model\TopDestinationCountries::class)
                ->disableOriginalConstructor()
                ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->layoutProcessor = $objectManager->getObject(
            \Magento\Checkout\Block\Cart\LayoutProcessor::class,
            [
                'merger' => $this->merger,
                'countryCollection' => $this->countryCollection,
                'regionCollection' => $this->regionCollection,
                'topDestinationCountries' => $this->topDestinationCountries
            ]
        );
    }

    public function testProcess()
    {
        $countries = [];
        $regions = [];
        $topDestinationCountries = ['UA','AF'];

        $layout = [];
        $layout['components']['block-summary']['children']['block-shipping']['children']
        ['address-fieldsets']['children'] = [
            'fieldOne' => ['param' => 'value'],
            'fieldTwo' => ['param' => 'value']
        ];
        $layoutPointer = &$layout['components']['block-summary']['children']['block-shipping']
        ['children']['address-fieldsets']['children'];

        $this->countryCollection->expects($this->once())->method('loadByStore')->willReturnSelf();
        $this->countryCollection
            ->expects($this->once())
            ->method('setForegroundCountries')
            ->with($topDestinationCountries)
            ->willReturnSelf();
        $this->countryCollection->expects($this->once())->method('toOptionArray')->willReturn($countries);

        $this->regionCollection->expects($this->once())->method('addAllowedCountriesFilter')->willReturnSelf();
        $this->regionCollection->expects($this->once())->method('toOptionArray')->willReturn($regions);

        $this->topDestinationCountries->expects($this->once())->method('getTopDestinations')
            ->willReturn($topDestinationCountries);

        $layoutMerged = $layout;
        $layoutMerged['components']['block-summary']['children']['block-shipping']['children']
        ['address-fieldsets']['children']['fieldThree'] = ['param' => 'value'];
        $layoutMergedPointer = &$layoutMerged['components']['block-summary']['children']['block-shipping']
        ['children']['address-fieldsets']['children'];
        $layoutMerged['components']['checkoutProvider'] = [
            'dictionaries' => [
                'country_id' => [],
                'region_id' => [],
            ]
        ];
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

        $this->assertEquals($layoutMerged, $this->layoutProcessor->process($layout));
    }
}
