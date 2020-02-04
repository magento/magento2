<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Observer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApplyThemeCustomizationObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeCustomization;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetRepo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetsMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Theme\Observer\ApplyThemeCustomizationObserver
     */
    protected $themeObserver;

    protected function setUp()
    {
        $this->themeCustomization = $this->createMock(\Magento\Framework\View\Design\Theme\Customization::class);
        $themeMock = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['__wakeup', 'getCustomization']);
        $themeMock->expects(
            $this->any()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($this->themeCustomization)
        );

        $designMock = $this->createMock(\Magento\Framework\View\DesignInterface::class);
        $designMock->expects($this->any())->method('getDesignTheme')->will($this->returnValue($themeMock));

        $this->assetsMock = $this->createMock(\Magento\Framework\View\Asset\GroupedCollection::class);

        $this->assetRepo = $this->createMock(\Magento\Framework\View\Asset\Repository::class);

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->themeObserver = $objectManagerHelper->getObject(
            \Magento\Theme\Observer\ApplyThemeCustomizationObserver::class,
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
        $asset = $this->createMock(\Magento\Framework\View\Asset\File::class);
        $file = $this->createMock(\Magento\Theme\Model\Theme\File::class);
        $fileService = $this->getMockForAbstractClass(
            \Magento\Framework\View\Design\Theme\Customization\FileAssetInterface::class
        );
        $file->expects($this->any())->method('getCustomizationService')->will($this->returnValue($fileService));

        $this->assetRepo->expects($this->once())
            ->method('createArbitrary')
            ->will($this->returnValue($asset));

        $this->themeCustomization->expects($this->once())->method('getFiles')->will($this->returnValue([$file]));
        $this->assetsMock->expects($this->once())->method('add')->with($this->anything(), $asset);

        $observer = new \Magento\Framework\Event\Observer();
        $this->themeObserver->execute($observer);
    }

    public function testApplyThemeCustomizationException()
    {
        $file = $this->createMock(\Magento\Theme\Model\Theme\File::class);
        $file->expects($this->any())
            ->method('getCustomizationService')
            ->willThrowException(new \InvalidArgumentException());

        $this->themeCustomization->expects($this->once())->method('getFiles')->will($this->returnValue([$file]));
        $this->logger->expects($this->once())->method('critical');

        $observer = new \Magento\Framework\Event\Observer();
        $this->themeObserver->execute($observer);
    }
}
