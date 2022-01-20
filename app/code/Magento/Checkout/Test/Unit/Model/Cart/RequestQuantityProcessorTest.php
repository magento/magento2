<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Cart;

use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Framework\Locale\ResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestQuantityProcessorTest extends TestCase
{
    /**
     * @var ResolverInterface | MockObject
     */
    private $localeResolver;

    /**
     * @var RequestQuantityProcessor
     */
    private $requestProcessor;

    protected function setUp(): void
    {
        $this->localeResolver = $this->getMockBuilder(ResolverInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * Test of cart data processing.
     *
     * @param array $cartData
     * @param string $locale
     * @param array $expected
     * @dataProvider cartDataProvider
     */
    public function testProcess(array $cartData, string $locale, array $expected): void
    {
        $this->localeResolver->method('getLocale')
            ->willReturn($locale);
        $this->requestProcessor = new RequestQuantityProcessor(
            $this->localeResolver
        );

        $this->assertEquals($this->requestProcessor->process($cartData), $expected);
    }

    /**
     * @return array
     */
    public function cartDataProvider()
    {
        return [
            'empty_array' => [
                'cartData' => [],
                'locale' => 'en_US',
                'expected' => [],
            ],
            'strings_array' => [
                'cartData' => [
                    ['qty' => ' 10 '],
                    ['qty' => ' 0.5 ']
                ],
                'locale' => 'en_US',
                'expected' => [
                    ['qty' => 10],
                    ['qty' => 0.5]
                ],
            ],
            'integer_array' => [
                'cartData' => [
                    ['qty' => 1],
                    ['qty' => 0.002]
                ],
                'locale' => 'en_US',
                'expected' => [
                    ['qty' => 1],
                    ['qty' => 0.002]
                ],
            ],
            'array_of arrays' => [
                'cartData' => [
                    ['qty' => [1, 2 ,3]],
                ],
                'locale' => 'en_US',
                'expected' => [
                    ['qty' => [1, 2, 3]],
                ],
            ],
            'strings_array_spain_locale' => [
                'cartData' => [
                    ['qty' => ' 10 '],
                    ['qty' => ' 0.5 ']
                ],
                'locale' => 'es_CL',
                'expected' => [
                    ['qty' => 10],
                    ['qty' => 0.5]
                ],
            ],
        ];
    }
}
