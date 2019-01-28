<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\LockerProcessInterface;
use Magento\Developer\Model\View\Asset\PreProcessor\FrontendCompilation;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSource\AssetBuilder;

/**
 * Class FrontendCompilationTest
 *
 * @see \Magento\Developer\Model\View\Asset\PreProcessor\FrontendCompilation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FrontendCompilationTest extends \PHPUnit\Framework\TestCase
{
    const AREA = 'test-area';

    const THEME = 'test-theme';

    const LOCALE = 'test-locale';

    const FILE_PATH = 'test-file';

    const MODULE = 'test-module';

    const NEW_CONTENT = 'test-new-content';

    /**
     * @var LockerProcessInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $lockerProcessMock;

    /**
     * @var AssetBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetBuilderMock;

    /**
     * @var AlternativeSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $alternativeSourceMock;

    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetSourceMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->lockerProcessMock = $this->getMockBuilder(LockerProcessInterface::class)
            ->getMockForAbstractClass();
        $this->assetBuilderMock = $this->getMockBuilder(AssetBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->alternativeSourceMock = $this->getMockBuilder(AlternativeSourceInterface::class)
            ->getMockForAbstractClass();
        $this->assetSourceMock = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Run test for process method (Exception)
     */
    public function testProcessException()
    {
        $this->lockerProcessMock->expects($this->once())
            ->method('lockProcess')
            ->with($this->isType('string'));
        $this->lockerProcessMock->expects($this->once())
            ->method('unlockProcess');

        $this->alternativeSourceMock->expects($this->once())
            ->method('getAlternativesExtensionsNames')
            ->willReturn(['less']);

        $this->assetBuilderMock->expects($this->once())
            ->method('setArea')
            ->with(self::AREA)
            ->willReturnSelf();
        $this->assetBuilderMock->expects($this->once())
            ->method('setTheme')
            ->with(self::THEME)
            ->willReturnSelf();
        $this->assetBuilderMock->expects($this->once())
            ->method('setLocale')
            ->with(self::LOCALE)
            ->willReturnSelf();
        $this->assetBuilderMock->expects($this->once())
            ->method('setModule')
            ->with(self::MODULE)
            ->willReturnSelf();
        $this->assetBuilderMock->expects($this->once())
            ->method('setPath')
            ->with(self::FILE_PATH)
            ->willReturnSelf();
        $this->assetBuilderMock->expects($this->once())
            ->method('build')
            ->willThrowException(new \Exception());

        $this->assetSourceMock->expects($this->never())
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
            $this->assertInstanceOf('\Exception', $e);
        }
    }

    /**
     * Run test for process method
     */
    public function testProcess()
    {
        $newContentType = 'less';

        $this->lockerProcessMock->expects($this->once())
            ->method('lockProcess')
            ->with($this->isType('string'));
        $this->lockerProcessMock->expects($this->once())
            ->method('unlockProcess');

        $assetMock = $this->getAssetNew();

        $this->assetBuilderMock->expects($this->once())
            ->method('setArea')
            ->with(self::AREA)
            ->willReturnSelf();
        $this->assetBuilderMock->expects($this->once())
            ->method('setTheme')
            ->with(self::THEME)
            ->willReturnSelf();
        $this->assetBuilderMock->expects($this->once())
            ->method('setLocale')
            ->with(self::LOCALE)
            ->willReturnSelf();
        $this->assetBuilderMock->expects($this->once())
            ->method('setModule')
            ->with(self::MODULE)
            ->willReturnSelf();
        $this->assetBuilderMock->expects($this->once())
            ->method('setPath')
            ->with(self::FILE_PATH)
            ->willReturnSelf();
        $this->assetBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($assetMock);

        $this->alternativeSourceMock->expects($this->once())
            ->method('getAlternativesExtensionsNames')
            ->willReturn([$newContentType]);

        $this->assetSourceMock->expects($this->once())
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
     * @return Chain|\PHPUnit_Framework_MockObject_MockObject
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
     * @return Chain|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getChainMockExpects($content = '', $contentExactly = 1, $pathExactly = 1, $newContentType = '')
    {
        $chainMock = $this->getChainMock();

        $chainMock->expects($this->once())
            ->method('getContent')
            ->willReturn($content);
        $chainMock->expects($this->exactly(3))
            ->method('getAsset')
            ->willReturn($this->getAssetMockExpects($pathExactly));
        $chainMock->expects($this->exactly($contentExactly))
            ->method('setContent')
            ->with(self::NEW_CONTENT);
        $chainMock->expects($this->exactly($contentExactly))
            ->method('setContentType')
            ->with($newContentType);

        return $chainMock;
    }

    /**
     * @return File|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAssetNew()
    {
        $assetMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $assetMock;
    }

    /**
     * @return LocalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAssetMock()
    {
        $assetMock = $this->getMockBuilder(LocalInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $assetMock;
    }

    /**
     * @param int $pathExactly
     * @return LocalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAssetMockExpects($pathExactly = 1)
    {
        $assetMock = $this->getAssetMock();

        $assetMock->expects($this->once())
            ->method('getContext')
            ->willReturn($this->getContextMock());
        $assetMock->expects($this->exactly($pathExactly))
            ->method('getFilePath')
            ->willReturn(self::FILE_PATH);
        $assetMock->expects($this->once())
            ->method('getModule')
            ->willReturn(self::MODULE);

        return $assetMock;
    }

    /**
     * @return FallbackContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContextMock()
    {
        $contextMock = $this->getMockBuilder(FallbackContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(self::AREA);
        $contextMock->expects($this->once())
            ->method('getThemePath')
            ->willReturn(self::THEME);
        $contextMock->expects($this->once())
            ->method('getLocale')
            ->willReturn(self::LOCALE);

        return $contextMock;
    }
}
