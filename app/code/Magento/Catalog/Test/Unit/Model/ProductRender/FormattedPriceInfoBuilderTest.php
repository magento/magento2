<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ProductRender;

use Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfo;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;
use Magento\Catalog\Model\ProductRender\PriceInfo;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormattedPriceInfoBuilderTest extends TestCase
{
    /**
     * @var PriceCurrencyInterface|MockObject ;
     */
    private $priceCurrencyMock;

    /**
     * @var FormattedPriceInfoInterfaceFactory|MockObject ;
     */
    private $formattedPriceInfoFactoryMock;

    /**
     * @var FormattedPriceInfoBuilder
     */
    private $formattedPriceInfoBuilderMock;

    protected function setUp(): void
    {
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $this->formattedPriceInfoFactoryMock = $this->createMock(FormattedPriceInfoInterfaceFactory::class);

        $this->formattedPriceInfoBuilderMock = new FormattedPriceInfoBuilder(
            $this->priceCurrencyMock,
            $this->formattedPriceInfoFactoryMock
        );
    }

    public function testBuild()
    {
        $storeId = 1;
        $storeCurrencyCode = 'USD';

        $formattedPriceInfoInterfaceMock = $this->createMock(
            FormattedPriceInfo::class
        );
        $priceInfoMock = $this->createPartialMock(PriceInfo::class, []);
        $priceInfoMock->setData('key', '1233123');
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
