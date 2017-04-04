<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Model;

use \Magento\Wishlist\Model\LocaleQuantityProcessor;

class LocaleQuantityProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocaleQuantityProcessor
     */
    protected $processor;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolver;

    /**
     * @var \Magento\Framework\Filter\LocalizedToNormalized|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    protected function setUp()
    {
        $this->resolver = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)->getMock();
        $this->filter   = $this->getMockBuilder(\Magento\Framework\Filter\LocalizedToNormalized::class)
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

    public function processDataProvider()
    {
        return [
            'positive' => [10.00, 10.00],
            'negative' => [0, null],
        ];
    }
}
