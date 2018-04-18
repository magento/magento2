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
class FrontendCompilationTest extends \PHPUnit_Framework_TestCase
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
        $this->lockerProcessMock->expects(self::once())
            ->method('lockProcess')
            ->with(self::isType('string'));
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
        $this->lockerProcessMock->expects(self::once())
            ->method('lockProcess')
            ->with(self::isType('string'));
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
            ->willReturn(['less']);

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

        $frontendCompilation->process($this->getChainMockExpects());
    }

    /**
     * Run test for process method (content not empty)
     */
    public function testProcessContentNotEmpty()
    {
        $chainMock = $this->getChainMock();
        $assetMock = $this->getAssetMock();

        $chainMock->expects(self::once())
            ->method('getContent')
            ->willReturn('test-content');

        $chainMock->expects(self::never())
            ->method('getAsset')
            ->willReturn($assetMock);

        $this->lockerProcessMock->expects(self::never())
            ->method('lockProcess');
        $this->lockerProcessMock->expects(self::never())
            ->method('unlockProcess');

        $frontendCompilation = new FrontendCompilation(
            $this->assetSourceMock,
            $this->assetBuilderMock,
            $this->alternativeSourceMock,
            $this->lockerProcessMock,
            'lock'
        );

        $frontendCompilation->process($chainMock);
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
     * @return Chain|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getChainMockExpects($content = '', $contentExactly = 1, $pathExactly = 1)
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
            ->willReturn(self::NEW_CONTENT);

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
     * @return FallbackContext|\PHPUnit_Framework_MockObject_MockObject
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
