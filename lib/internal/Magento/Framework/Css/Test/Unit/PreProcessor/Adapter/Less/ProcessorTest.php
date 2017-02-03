<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 */
class ProcessorTest extends \PHPUnit_Framework_TestCase
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
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetSourceMock;

    /**
     * @var Temporary|\PHPUnit_Framework_MockObject_MockObject
     */
    private $temporaryFileMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMockForAbstractClass();
        $this->appStateMock = $this->getMockBuilder('Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetSourceMock = $this->getMockBuilder('Magento\Framework\View\Asset\Source')
            ->disableOriginalConstructor()
            ->getMock();
        $this->temporaryFileMock = $this->getMockBuilder('Magento\Framework\Css\PreProcessor\File\Temporary')
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
     * @expectedException \Magento\Framework\View\Asset\ContentProcessorException
     * @expectedExceptionMessageRegExp (Compilation from source:.*Test exception)
     */
    public function testProcessContentException()
    {
        $assetMock = $this->getAssetMock();

        $this->appStateMock->expects(self::once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($assetMock)
            ->willThrowException(new \Exception(self::ERROR_MESSAGE));

        $this->loggerMock->expects(self::once())
            ->method('critical')
            ->with(
                PHP_EOL . Processor::ERROR_MESSAGE_PREFIX . PHP_EOL . self::ASSET_PATH  . PHP_EOL . self::ERROR_MESSAGE
            );

        $this->temporaryFileMock->expects(self::never())
            ->method('createFile');

        $assetMock->expects(self::once())
            ->method('getPath')
            ->willReturn(self::ASSET_PATH);

        $this->processor->processContent($assetMock);
    }

    /**
     * Test for processContent method (empty content)
     */
    public function testProcessContentEmpty()
    {
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
     * @return File|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAssetMock()
    {
        $assetMock = $this->getMockBuilder('Magento\Framework\View\Asset\File')
            ->disableOriginalConstructor()
            ->getMock();

        return $assetMock;
    }
}
