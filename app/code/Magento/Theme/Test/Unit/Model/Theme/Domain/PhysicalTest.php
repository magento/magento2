<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme domain physical model
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Domain;

class PhysicalTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateVirtualTheme()
    {
        $physicalTheme = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['__wakeup']);
        $physicalTheme->setData(['parent_id' => 10, 'theme_title' => 'Test Theme']);

        $copyService = $this->createPartialMock(\Magento\Theme\Model\CopyService::class, ['copy']);
        $copyService->expects($this->once())->method('copy')->willReturn($copyService);

        $virtualTheme = $this->createPartialMock(
            \Magento\Theme\Model\Theme::class,
            ['__wakeup', 'getThemeImage', 'createPreviewImageCopy', 'save']
        );
        $virtualTheme->expects($this->once())->method('getThemeImage')->willReturn($virtualTheme);

        $virtualTheme->expects(
            $this->once()
        )->method(
            'createPreviewImageCopy'
        )->willReturn(
            $virtualTheme
        );

        $virtualTheme->expects($this->once())->method('save')->willReturn($virtualTheme);

        $themeFactory = $this->createPartialMock(\Magento\Theme\Model\ThemeFactory::class, ['create']);
        $themeFactory->expects($this->once())->method('create')->willReturn($virtualTheme);

        $themeCollection = $this->createPartialMock(
            \Magento\Theme\Model\ResourceModel\Theme\Collection::class,
            ['addTypeFilter', 'addAreaFilter', 'addFilter', 'count']
        );

        $themeCollection->expects($this->any())->method('addTypeFilter')->willReturn($themeCollection);

        $themeCollection->expects($this->any())->method('addAreaFilter')->willReturn($themeCollection);

        $themeCollection->expects($this->any())->method('addFilter')->willReturn($themeCollection);

        $themeCollection->expects($this->once())->method('count')->willReturn(1);

        $domainModel = new \Magento\Theme\Model\Theme\Domain\Physical(
            $this->createMock(\Magento\Framework\View\Design\ThemeInterface::class),
            $themeFactory,
            $copyService,
            $themeCollection
        );
        $domainModel->createVirtualTheme($physicalTheme);
    }
}
