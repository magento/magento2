<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Checkout\Block\Cart\LayoutProcessor;
use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\TopDestinationCountries;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LayoutProcessorTest extends TestCase
{
    /**
     * @var LayoutProcessor
     */
    private $layoutProcessor;

    /**
     * @var LayoutProcessor
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $merger;

    /**
     * @var MockObject
     */
    protected $countryCollection;

    /**
     * @var MockObject
     */
    protected $regionCollection;

    /**
     * @var MockObject
     */
    protected $topDestinationCountries;

    protected function setUp(): void
    {
        $this->merger = $this->getMockBuilder(AttributeMerger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->countryCollection =
            $this->getMockBuilder(Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->regionCollection =
            $this->getMockBuilder(\Magento\Directory\Model\ResourceModel\Region\Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->topDestinationCountries =
            $this->getMockBuilder(TopDestinationCountries::class)
                ->disableOriginalConstructor()
                ->getMock();
        $objectManager = new ObjectManager($this);
        $this->layoutProcessor = $objectManager->getObject(
            LayoutProcessor::class,
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
