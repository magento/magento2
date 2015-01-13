<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model;

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

    public function setUp()
    {
        $this->resolver = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')->getMock();
        $this->filter   = $this->getMockBuilder('Magento\Framework\Filter\LocalizedToNormalized')
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
            ->method('getLocaleCode')
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
