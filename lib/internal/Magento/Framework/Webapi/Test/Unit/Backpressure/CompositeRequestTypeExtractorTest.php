<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Backpressure;

use Magento\Framework\Webapi\Backpressure\BackpressureRequestTypeExtractorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Webapi\Backpressure\CompositeRequestTypeExtractor;

/**
 * Tests the CompositeRequestTypeExtractor class
 */
class CompositeRequestTypeExtractorTest extends TestCase
{
    /**
     * @var CompositeRequestTypeExtractor
     */
    private CompositeRequestTypeExtractor $compositeRequestTypeExtractor;

    /**
     * @var BackpressureRequestTypeExtractorInterface|MockObject
     */
    private $extractorMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->extractorMock = $this->getMockForAbstractClass(
            BackpressureRequestTypeExtractorInterface::class
        );

        $this->compositeRequestTypeExtractor = new CompositeRequestTypeExtractor(
            array_fill(0, 3, $this->extractorMock)
        );
    }

    /**
     * Tests CompositeRequestTypeExtractor
     */
    public function testExtract()
    {
        $this->extractorMock->expects($this->exactly(2))
            ->method('extract')
            ->with('someService', 'someMethod', 'someEndpoint')
            ->willReturnOnConsecutiveCalls(null, 'someType');

        $this->assertEquals(
            'someType',
            $this->compositeRequestTypeExtractor->extract(
                'someService',
                'someMethod',
                'someEndpoint'
            )
        );
    }

    /**
     * Tests CompositeRequestTypeExtractor when type
     */
    public function testExtractTypeNotFound()
    {
        $this->extractorMock->expects($this->exactly(3))
            ->method('extract')
            ->with('someService', 'someMethod', 'someEndpoint')
            ->willReturn(null);
        $this->assertEquals(
            null,
            $this->compositeRequestTypeExtractor->extract(
                'someService',
                'someMethod',
                'someEndpoint'
            )
        );
    }
}
