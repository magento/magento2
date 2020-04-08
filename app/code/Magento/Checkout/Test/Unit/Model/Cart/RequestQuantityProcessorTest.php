<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Cart;

use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Framework\Locale\ResolverInterface;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class RequestQuantityProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResolverInterface | MockObject
     */
    private $localeResolver;

    /**
     * @var RequestQuantityProcessor
     */
    private $requestProcessor;

    protected function setUp()
    {
        $this->localeResolver = $this->getMockBuilder(ResolverInterface::class)
            ->getMockForAbstractClass();

        $this->localeResolver->method('getLocale')
            ->willReturn('en_US');

        $this->requestProcessor = new RequestQuantityProcessor(
            $this->localeResolver
        );
    }

    /**
     * Test of cart data processing.
     *
     * @param array $cartData
     * @param array $expected
     * @dataProvider cartDataProvider
     */
    public function testProcess($cartData, $expected)
    {
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
                'expected' => [],
            ],
            'strings_array' => [
                'cartData' => [
                    ['qty' => ' 10 '],
                    ['qty' => ' 0.5 ']
                ],
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
                'expected' => [
                    ['qty' => 1],
                    ['qty' => 0.002]
                ],
            ],
            'array_of arrays' => [
                'cartData' => [
                    ['qty' => [1, 2 ,3]],
                ],
                'expected' => [
                    ['qty' => [1, 2, 3]],
                ],
            ],
        ];
    }
}
