<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\Test\Unit\PreProcessor\Adapter\Less;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Framework\Css\PreProcessor\Adapter\Less\Processor;

/**
 * Class ProcessorTest
 * Test for Processer
 */
class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    const TEST_CONTENT = 'test-content';

    const ASSET_PATH = 'test-path';

    const TMP_PATH_LESS = '_file/test.less';

    const TMP_PATH_CSS = '_file/test.css';

    const ERROR_MESSAGE = 'Test exception';

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var State|\PHPUnit\Framework\MockObject\MockObject
     */
    private $appStateMock;

    /**
     * @var Source|\PHPUnit\Framework\MockObject\MockObject
     */
    private $assetSourceMock;

    /**
     * @var Temporary|\PHPUnit\Framework\MockObject\MockObject
     */
    private $temporaryFileMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetSourceMock = $this->getMockBuilder(\Magento\Framework\View\Asset\Source::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->temporaryFileMock = $this->getMockBuilder(\Magento\Framework\Css\PreProcessor\File\Temporary::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new Processor(
            $this->loggerMock,
            $this->appStateMock,
            $this->assetSourceMock,
            $this->temporaryFileMock
        );
    }

    /**
     * Test for processContent method (exception)
     *
     */
    public function testProcessContentException()
    {
        $this->expectException(\Magento\Framework\View\Asset\ContentProcessorException::class);
        $this->expectExceptionMessageMatches('(Test exception)');

        $assetMock = $this->getAssetMock();

        $this->appStateMock->expects(self::once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($assetMock)
            ->willThrowException(new \Exception(self::ERROR_MESSAGE));

        $this->loggerMock->expects(self::never())
            ->method('critical');

        $this->temporaryFileMock->expects(self::never())
            ->method('createFile');

        $assetMock->expects(self::once())
            ->method('getPath')
            ->willReturn(self::ASSET_PATH);

        $this->processor->processContent($assetMock);
    }

    /**
     * Test for processContent method (empty content)
     *
     */
    public function testProcessContentEmpty()
    {
        $this->expectException(\Magento\Framework\View\Asset\ContentProcessorException::class);
        $this->expectExceptionMessageMatches('(Compilation from source: LESS file is empty: test-path)');

        $assetMock = $this->getAssetMock();

        $this->appStateMock->expects(self::once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($assetMock)
            ->willReturn('');

        $this->temporaryFileMock->expects(self::never())
            ->method('createFile');

        $assetMock->expects(self::once())
            ->method('getPath')
            ->willReturn(self::ASSET_PATH);

        $this->loggerMock->expects(self::never())
            ->method('critical');

        $this->processor->processContent($assetMock);
    }

    /**
     * Test for processContent method (not empty content)
     */
    public function testProcessContentNotEmpty()
    {
        $assetMock = $this->getAssetMock();

        $this->appStateMock->expects(self::once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($assetMock)
            ->willReturn(self::TEST_CONTENT);

        $this->temporaryFileMock->expects(self::once())
            ->method('createFile')
            ->with(self::ASSET_PATH, self::TEST_CONTENT)
            ->willReturn(__DIR__ . '/' . self::TMP_PATH_LESS);

        $assetMock->expects(self::once())
            ->method('getPath')
            ->willReturn(self::ASSET_PATH);

        $this->loggerMock->expects(self::never())
            ->method('critical');

        $clearSymbol = ["\n", "\r", "\t", ' '];
        self::assertEquals(
            trim(str_replace($clearSymbol, '', file_get_contents(__DIR__ . '/' . self::TMP_PATH_CSS))),
            trim(str_replace($clearSymbol, '', $this->processor->processContent($assetMock)))
        );
    }

    /**
     * @return File|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getAssetMock()
    {
        $assetMock = $this->getMockBuilder(\Magento\Framework\View\Asset\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $assetMock;
    }
}
