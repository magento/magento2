<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Block;

use Magento\Directory\Block\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @var Currency
     */
    protected $object;

    /**
     * @var MockObject
     */
    protected $postDataHelperMock;

    /**
     * @var MockObject
     */
    protected $urlBuilderMock;

    protected function setUp(): void
    {
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->urlBuilderMock->expects($this->any())->method('getUrl')->willReturnArgument(0);

        /** @var Context|MockObject $contextMock */
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $escaperMock = $this->createMock(Escaper::class);
        $escaperMock->method('escapeUrl')
            ->willReturnCallback(
                function ($string) {
                    return 'escapeUrl' . $string;
                }
            );
        $contextMock->expects($this->once())
            ->method('getEscaper')
            ->willReturn($escaperMock);

        /** @var CurrencyFactory $currencyFactoryMock */
        $currencyFactoryMock = $this->createMock(CurrencyFactory::class);
        $this->postDataHelperMock = $this->createMock(PostHelper::class);

        /** @var ResolverInterface $localeResolverMock */
        $localeResolverMock = $this->createMock(ResolverInterface::class);

        $this->object = new Currency(
            $contextMock,
            $currencyFactoryMock,
            $this->postDataHelperMock,
            $localeResolverMock
        );
    }

    public function testGetSwitchCurrencyPostData()
    {
        $expectedResult = 'post_data';
        $expectedCurrencyCode = 'test';
        $switchUrl = 'escapeUrldirectory/currency/switch';

        $this->postDataHelperMock->expects($this->once())
            ->method('getPostData')
            ->with($switchUrl, ['currency' => $expectedCurrencyCode])
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->object->getSwitchCurrencyPostData($expectedCurrencyCode));
    }
}
