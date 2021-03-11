<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Asset\PreProcessor;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\PreProcessor\Pool;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\View\Asset\PreProcessor\Helper\SortInterface;

/**
 * Class PoolTest
 *
 * @see \Magento\Framework\View\Asset\PreProcessor\Pool
 */
class PoolTest extends \PHPUnit\Framework\TestCase
{
    const DEFAULT_PREPROCESSOR = 'defaul/preprocessor';

    const CONTENT_TYPE = 'test-type';

    const PREPROCESSOR_CLASS = \Magento\Framework\View\Asset\PreProcessorInterface::class;

    /**
     * @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /**
     * @var SortInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sorterMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->sorterMock = $this->getMockBuilder(
            \Magento\Framework\View\Asset\PreProcessor\Helper\SortInterface::class
        )->getMockForAbstractClass();
    }

    /**
     * @return Chain|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getChainMock($type)
    {
        /** @var Chain|\PHPUnit\Framework\MockObject\MockObject $chainMock */
        $chainMock = $this->getMockBuilder(\Magento\Framework\View\Asset\PreProcessor\Chain::class)
            ->disableOriginalConstructor()
            ->getMock();

        $chainMock->expects(self::once())
            ->method('getTargetContentType')
            ->willReturn($type);

        return $chainMock;
    }

    /**
     * @param Chain|\PHPUnit\Framework\MockObject\MockObject $chainMock
     * @return PreProcessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getPreprocessorMock($chainMock)
    {
        /** @var PreProcessorInterface|\PHPUnit\Framework\MockObject\MockObject $preprocessorMock */
        $preprocessorMock = $this->getMockBuilder(self::PREPROCESSOR_CLASS)
            ->getMockForAbstractClass();

        $preprocessorMock->expects(self::once())
            ->method('process')
            ->with($chainMock);

        return $preprocessorMock;
    }

    /**
     * Run test for process method
     */
    public function testProcess()
    {
        $preprocessors = [
            self::CONTENT_TYPE => [
                'test' => [
                    Pool::PREPROCESSOR_CLASS => self::PREPROCESSOR_CLASS
                ]
            ]
        ];

        $pool = new Pool(
            $this->objectManagerMock,
            $this->sorterMock,
            self::DEFAULT_PREPROCESSOR,
            $preprocessors
        );

        $this->sorterMock->expects(self::once())
            ->method('sort')
            ->with($preprocessors[self::CONTENT_TYPE])
            ->willReturn($preprocessors[self::CONTENT_TYPE]);

        $chainMock = $this->getChainMock(self::CONTENT_TYPE);

        $this->objectManagerMock->expects(self::once())
            ->method('get')
            ->with(self::PREPROCESSOR_CLASS)
            ->willReturn($this->getPreprocessorMock($chainMock));

        $pool->process($chainMock);
    }

    /**
     * Run test for process method (default preprocessor)
     */
    public function testProcessDefault()
    {
        $preprocessors = [
            'bad-type' => [],
        ];

        $pool = new Pool(
            $this->objectManagerMock,
            $this->sorterMock,
            self::DEFAULT_PREPROCESSOR,
            $preprocessors
        );

        $this->sorterMock->expects(self::never())
            ->method('sort');

        $chainMock = $this->getChainMock(self::CONTENT_TYPE);

        $this->objectManagerMock->expects(self::once())
            ->method('get')
            ->with(self::DEFAULT_PREPROCESSOR)
            ->willReturn($this->getPreprocessorMock($chainMock));

        $pool->process($chainMock);
    }

    /**
     * Run test for process method (exception)
     *
     */
    public function testProcessBadInterface()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('"stdClass" has to implement the PreProcessorInterface.');

        $preprocessors = [
            self::CONTENT_TYPE => [
                'test' => [
                    Pool::PREPROCESSOR_CLASS => 'stdClass'
                ]
            ]
        ];

        $pool = new Pool(
            $this->objectManagerMock,
            $this->sorterMock,
            self::DEFAULT_PREPROCESSOR,
            $preprocessors
        );

        $this->sorterMock->expects(self::once())
            ->method('sort')
            ->with($preprocessors[self::CONTENT_TYPE])
            ->willReturn($preprocessors[self::CONTENT_TYPE]);

        $chainMock = $this->getChainMock(self::CONTENT_TYPE);

        $this->objectManagerMock->expects(self::once())
            ->method('get')
            ->with('stdClass')
            ->willReturn(new \stdClass());

        $pool->process($chainMock);
    }
}
