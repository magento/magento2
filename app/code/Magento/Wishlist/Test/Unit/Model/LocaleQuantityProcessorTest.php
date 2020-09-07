<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model;

use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LocaleQuantityProcessorTest extends TestCase
{
    /**
     * @var LocaleQuantityProcessor
     */
    protected $processor;

    /**
     * @var ResolverInterface|MockObject
     */
    protected $resolver;

    /**
     * @var LocalizedToNormalized|MockObject
     */
    protected $filter;

    protected function setUp(): void
    {
        $this->resolver = $this->getMockBuilder(ResolverInterface::class)
            ->getMock();
        $this->filter   = $this->getMockBuilder(LocalizedToNormalized::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processor = new LocaleQuantityProcessor($this->resolver, $this->filter);
    }

    /**
     * @param int $qtyResult
     * @param int|null $expectedResult
     * @dataProvider processDataProvider
     */
    public function testProcess($qtyResult, $expectedResult)
    {
        $qty = 10;
        $localCode = 'en_US';

        $this->resolver->expects($this->once())
            ->method('getLocale')
            ->willReturn($localCode);

        $this->filter->expects($this->once())
            ->method('setOptions')
            ->with(['locale' => $localCode])
            ->willReturnSelf();

        $this->filter->expects($this->once())
            ->method('filter')
            ->with($qty)
            ->willReturn($qtyResult);

        $this->assertEquals($expectedResult, $this->processor->process($qty));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'positive' => [10.00, 10.00],
            'negative' => [0, null],
        ];
    }
}
