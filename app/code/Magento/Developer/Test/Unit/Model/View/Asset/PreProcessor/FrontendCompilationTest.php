<?php declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Developer\Test\Unit\Model\View\Asset\PreProcessor;

use Magento\Developer\Model\View\Asset\PreProcessor\FrontendCompilation;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\LockerProcessInterface;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSource\AssetBuilder;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\Source;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see \Magento\Developer\Model\View\Asset\PreProcessor\FrontendCompilation
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FrontendCompilationTest extends TestCase
{
    private const AREA = 'test-area';

    private const THEME = 'test-theme';

    private const LOCALE = 'test-locale';

    private const FILE_PATH = 'test-file';

    private const MODULE = 'test-module';

    private const NEW_CONTENT = 'test-new-content';

    /**
     * @var LockerProcessInterface|MockObject
     */
    private $lockerProcessMock;

    /**
     * @var AssetBuilder|MockObject
     */
    private $assetBuilderMock;

    /**
     * @var AlternativeSourceInterface|MockObject
     */
    private $alternativeSourceMock;

    /**
     * @var Source|MockObject
     */
    private $assetSourceMock;

    protected function setUp(): void
    {
        $this->lockerProcessMock = $this->createMock(LockerProcessInterface::class);
        $this->assetBuilderMock = $this->getMockBuilder(AssetBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->alternativeSourceMock = $this->createMock(AlternativeSourceInterface::class);
        $this->assetSourceMock = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Run test for process method (Exception)
     */
    public function testProcessException()
    {
        $this->lockerProcessMock->expects(self::once())
            ->method('lockProcess')
            ->with($this->callback('is_string'));
        $this->lockerProcessMock->expects(self::once())
            ->method('unlockProcess');

        $this->alternativeSourceMock->expects(self::once())
            ->method('getAlternativesExtensionsNames')
            ->willReturn(['less']);

        $this->assetBuilderMock->expects(self::once())
            ->method('setArea')
            ->with(self::AREA)
            ->willReturnSelf();
        $this->assetBuilderMock->expects(self::once())
            ->method('setTheme')
            ->with(self::THEME)
            ->willReturnSelf();
        $this->assetBuilderMock->expects(self::once())
            ->method('setLocale')
            ->with(self::LOCALE)
            ->willReturnSelf();
        $this->assetBuilderMock->expects(self::once())
            ->method('setModule')
            ->with(self::MODULE)
            ->willReturnSelf();
        $this->assetBuilderMock->expects(self::once())
            ->method('setPath')
            ->with(self::FILE_PATH)
            ->willReturnSelf();
        $this->assetBuilderMock->expects(self::once())
            ->method('build')
            ->willThrowException(new \Exception());

        $this->assetSourceMock->expects(self::never())
            ->method('getContent');

        $frontendCompilation = new FrontendCompilation(
            $this->assetSourceMock,
            $this->assetBuilderMock,
            $this->alternativeSourceMock,
            $this->lockerProcessMock,
            'lock'
        );

        try {
            $frontendCompilation->process($this->getChainMockExpects('', 0, 1));
        } catch (\Exception $e) {
            self::assertInstanceOf('\Exception', $e);
        }
    }

    /**
     * Run test for process method
     */
    public function testProcess()
    {
        $newContentType = 'less';

        $this->lockerProcessMock->expects(self::once())
            ->method('lockProcess')
            ->with($this->callback('is_string'));
        $this->lockerProcessMock->expects(self::once())
            ->method('unlockProcess');

        $assetMock = $this->getAssetNew();

        $this->assetBuilderMock->expects(self::once())
            ->method('setArea')
            ->with(self::AREA)
            ->willReturnSelf();
        $this->assetBuilderMock->expects(self::once())
            ->method('setTheme')
            ->with(self::THEME)
            ->willReturnSelf();
        $this->assetBuilderMock->expects(self::once())
            ->method('setLocale')
            ->with(self::LOCALE)
            ->willReturnSelf();
        $this->assetBuilderMock->expects(self::once())
            ->method('setModule')
            ->with(self::MODULE)
            ->willReturnSelf();
        $this->assetBuilderMock->expects(self::once())
            ->method('setPath')
            ->with(self::FILE_PATH)
            ->willReturnSelf();
        $this->assetBuilderMock->expects(self::once())
            ->method('build')
            ->willReturn($assetMock);

        $this->alternativeSourceMock->expects(self::once())
            ->method('getAlternativesExtensionsNames')
            ->willReturn([$newContentType]);

        $this->assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($assetMock)
            ->willReturn(self::NEW_CONTENT);

        $frontendCompilation = new FrontendCompilation(
            $this->assetSourceMock,
            $this->assetBuilderMock,
            $this->alternativeSourceMock,
            $this->lockerProcessMock,
            'lock'
        );

        $frontendCompilation->process($this->getChainMockExpects('', 1, 1, $newContentType));
    }

    /**
     * @return Chain|MockObject
     */
    private function getChainMock()
    {
        $chainMock = $this->getMockBuilder(Chain::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $chainMock;
    }

    /**
     * @param string $content
     * @param int $contentExactly
     * @param int $pathExactly
     * @param string $newContentType
     * @return Chain|MockObject
     */
    private function getChainMockExpects($content = '', $contentExactly = 1, $pathExactly = 1, $newContentType = '')
    {
        $chainMock = $this->getChainMock();

        $chainMock->expects(self::once())
            ->method('getContent')
            ->willReturn($content);
        $chainMock->expects(self::exactly(3))
            ->method('getAsset')
            ->willReturn($this->getAssetMockExpects($pathExactly));
        $chainMock->expects(self::exactly($contentExactly))
            ->method('setContent')
            ->with(self::NEW_CONTENT);
        $chainMock->expects(self::exactly($contentExactly))
            ->method('setContentType')
            ->with($newContentType);

        return $chainMock;
    }

    /**
     * @return File|MockObject
     */
    private function getAssetNew()
    {
        $assetMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $assetMock;
    }

    /**
     * @return LocalInterface|MockObject
     */
    private function getAssetMock()
    {
        $assetMock = $this->createMock(LocalInterface::class);

        return $assetMock;
    }

    /**
     * @param int $pathExactly
     * @return LocalInterface|MockObject
     */
    private function getAssetMockExpects($pathExactly = 1)
    {
        $assetMock = $this->getAssetMock();

        $assetMock->expects(self::once())
            ->method('getContext')
            ->willReturn($this->getContextMock());
        $assetMock->expects(self::exactly($pathExactly))
            ->method('getFilePath')
            ->willReturn(self::FILE_PATH);
        $assetMock->expects(self::once())
            ->method('getModule')
            ->willReturn(self::MODULE);

        return $assetMock;
    }

    /**
     * @return FallbackContext|MockObject
     */
    private function getContextMock()
    {
        $contextMock = $this->getMockBuilder(FallbackContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects(self::once())
            ->method('getAreaCode')
            ->willReturn(self::AREA);
        $contextMock->expects(self::once())
            ->method('getThemePath')
            ->willReturn(self::THEME);
        $contextMock->expects(self::once())
            ->method('getLocale')
            ->willReturn(self::LOCALE);

        return $contextMock;
    }
}
