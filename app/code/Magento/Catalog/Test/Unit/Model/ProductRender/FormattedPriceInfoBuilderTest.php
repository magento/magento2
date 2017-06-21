<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ProductRender;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoInterfaceFactory;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;

class FormattedPriceInfoBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject;
     */
    private $priceCurrencyMock;

    /**
     * @var FormattedPriceInfoInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject;
     */
    private $formattedPriceInfoFactoryMock;

    /**
     * @var FormattedPriceInfoBuilder
     */
    private $formattedPriceInfoBuilderMock;

    public function setUp()
    {
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMockForAbstractClass();
        $this->formattedPriceInfoFactoryMock = $this->getMockBuilder(FormattedPriceInfoInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formattedPriceInfoBuilderMock = new FormattedPriceInfoBuilder(
            $this->priceCurrencyMock,
            $this->formattedPriceInfoFactoryMock
        );
    }

    public function testBuild()
    {
        $storeId = 1;
        $storeCurrencyCode = 'USD';

        $formattedPriceInfoInterfaceMock = $this->getMockBuilder(FormattedPriceInfoInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $priceInfoMock = $this->getMockBuilder(PriceInfoInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $priceInfoMock->expects($this->any())
            ->method('getData')
            ->willReturn([
                'key'=>'1233123'
            ]);
        $this->priceCurrencyMock->expects($this->atLeastOnce())
            ->method('format')
            ->with(
                '1233123',
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $storeId,
                $storeCurrencyCode
            )
            ->willReturn(12.1);
        $formattedPriceInfoInterfaceMock->expects($this->atLeastOnce())
            ->method('setData')
            ->with('key', 12.1);
        $this->formattedPriceInfoFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($formattedPriceInfoInterfaceMock);

        $this->formattedPriceInfoBuilderMock->build($priceInfoMock, $storeId, $storeCurrencyCode);
    }
}
