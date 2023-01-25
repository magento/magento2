<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\Theme\Customization;
use Magento\Framework\View\Design\Theme\Customization\FileAssetInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Theme\Model\Theme;
use Magento\Theme\Observer\ApplyThemeCustomizationObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApplyThemeCustomizationObserverTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $themeCustomization;

    /**
     * @var MockObject
     */
    protected $assetRepo;

    /**
     * @var MockObject
     */
    protected $assetsMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * @var ApplyThemeCustomizationObserver
     */
    protected $themeObserver;

    protected function setUp(): void
    {
        $this->themeCustomization = $this->createMock(Customization::class);
        $themeMock = $this->createPartialMock(Theme::class, ['__wakeup', 'getCustomization']);
        $themeMock->expects(
            $this->any()
        )->method(
            'getCustomization'
        )->willReturn(
            $this->themeCustomization
        );

        $designMock = $this->getMockForAbstractClass(DesignInterface::class);
        $designMock->expects($this->any())->method('getDesignTheme')->willReturn($themeMock);

        $this->assetsMock = $this->createMock(GroupedCollection::class);

        $this->assetRepo = $this->createMock(Repository::class);

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->themeObserver = $objectManagerHelper->getObject(
            ApplyThemeCustomizationObserver::class,
            [
                'design' => $designMock,
                'assets' => $this->assetsMock,
                'assetRepo' => $this->assetRepo,
                'logger' => $this->logger,
            ]
        );
    }

    public function testApplyThemeCustomization()
    {
        $asset = $this->createMock(File::class);
        $file = $this->createMock(\Magento\Theme\Model\Theme\File::class);
        $fileService = $this->getMockForAbstractClass(
            FileAssetInterface::class
        );
        $file->expects($this->any())->method('getCustomizationService')->willReturn($fileService);

        $this->assetRepo->expects($this->once())
            ->method('createArbitrary')
            ->willReturn($asset);

        $this->themeCustomization->expects($this->once())->method('getFiles')->willReturn([$file]);
        $this->assetsMock->expects($this->once())->method('add')->with($this->anything(), $asset);

        $observer = new Observer();
        $this->themeObserver->execute($observer);
    }

    public function testApplyThemeCustomizationException()
    {
        $file = $this->createMock(\Magento\Theme\Model\Theme\File::class);
        $file->expects($this->any())
            ->method('getCustomizationService')
            ->willThrowException(new \InvalidArgumentException());

        $this->themeCustomization->expects($this->once())->method('getFiles')->willReturn([$file]);
        $this->logger->expects($this->once())->method('critical');

        $observer = new Observer();
        $this->themeObserver->execute($observer);
    }
}
