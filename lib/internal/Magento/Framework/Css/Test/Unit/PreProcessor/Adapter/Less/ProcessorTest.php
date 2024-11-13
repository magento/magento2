<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Css\Test\Unit\PreProcessor\Adapter\Less;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Css\PreProcessor\Adapter\Less\Processor;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Source;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ProcessorTest extends TestCase
{
    private const TEST_CONTENT = 'test-content';

    private const ASSET_PATH = 'test-path';

    private const TMP_PATH_LESS = '_file/test.less';
    private const TMP_PATH_CSS_PRODUCTION = '_file/test-production.css';
    private const TMP_PATH_CSS_DEVELOPER = '_file/test-developer.css';

    private const ERROR_MESSAGE = 'Test exception';

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var Source|MockObject
     */
    private $assetSourceMock;

    /**
     * @var Temporary|MockObject
     */
    private $temporaryFileMock;
    /**
     * @var DirectoryList|MockObject
     */
    private $directoryListMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->appStateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetSourceMock = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->temporaryFileMock = $this->getMockBuilder(Temporary::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryListMock = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new Processor(
            $this->loggerMock,
            $this->appStateMock,
            $this->assetSourceMock,
            $this->temporaryFileMock,
            $this->directoryListMock,
        );
    }

    /**
     * Test for processContent method (exception)
     */
    public function testProcessContentException()
    {
        $this->expectException('Magento\Framework\View\Asset\ContentProcessorException');
        $this->expectExceptionMessageMatches('(Test exception)');
        $assetMock = $this->getAssetMock();

        $this->appStateMock->expects(self::once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

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
     */
    public function testProcessContentEmpty()
    {
        $this->expectException('Magento\Framework\View\Asset\ContentProcessorException');
        $this->expectExceptionMessageMatches('(Compilation from source: LESS file is empty: test-path)');
        $assetMock = $this->getAssetMock();

        $this->appStateMock->expects(self::once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

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
     * Test for processContent method in production mode (not empty content)
     */
    public function testProcessContentNotEmpty()
    {
        $assetMock = $this->getAssetMock();

        $this->appStateMock->expects(self::once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

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
            trim(str_replace(
                $clearSymbol,
                '',
                file_get_contents(__DIR__ . '/' . self::TMP_PATH_CSS_PRODUCTION)
            )),
            trim(str_replace(
                $clearSymbol,
                '',
                $this->processor->processContent($assetMock)
            ))
        );
    }

    /**
     * Test for processContent method in developer mode (not empty content)
     */
    public function testProcessContentNotEmptyInDeveloperMode()
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
            trim(str_replace(
                $clearSymbol,
                '',
                file_get_contents(__DIR__ . '/' . self::TMP_PATH_CSS_DEVELOPER)
            )),
            trim(str_replace(
                $clearSymbol,
                '',
                $this->normalizeInlineSourceMap($this->processor->processContent($assetMock))
            ))
        );
    }

    /**
     * @return File|MockObject
     */
    private function getAssetMock()
    {
        $assetMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $assetMock;
    }

    /**
     * - find json part of sourcemap
     * - url decode it
     * - replace \/ with / in source filenames
     * - remove absolute path in filename, make it a relative path
     */
    private function normalizeInlineSourceMap(string $css): string
    {
        $regexBegin = 'sourceMappingURL=data:application/json,';
        $regexEnd = '*/';
        $regex = '@' . preg_quote($regexBegin, '@') . '([^\*]+)' . preg_quote($regexEnd, '@') . '@';

        if (preg_match($regex, $css, $matches) === 1) {
            $inlineSourceMapJson = $matches[1];
            $inlineSourceMapJson = urldecode($inlineSourceMapJson);
            $inlineSourceMapJson = json_decode($inlineSourceMapJson, true, 512, JSON_UNESCAPED_SLASHES);

            $relativeFilenames = [];
            foreach ($inlineSourceMapJson['sources'] as $filename) {
                $relativeFilenames[] = str_replace(sprintf('%s/', BP), '', $filename);
            }
            $inlineSourceMapJson['sources'] = $relativeFilenames;
            $inlineSourceMapJson = json_encode($inlineSourceMapJson, JSON_UNESCAPED_SLASHES);

            $css = preg_replace($regex, sprintf('%s%s%s', $regexBegin, $inlineSourceMapJson, $regexEnd), $css);
        }

        return $css;
    }
}
