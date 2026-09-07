<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test theme domain physical model
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Domain;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Theme\Model\CopyService;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\Domain\Physical;
use PHPUnit\Framework\TestCase;

class PhysicalTest extends TestCase
{
    use MockCreationTrait;

    public function testCreateVirtualTheme()
    {
        $physicalTheme = $this->createPartialMock(Theme::class, ['__wakeup']);
        $physicalTheme->setData(['parent_id' => 10, 'theme_title' => 'Test Theme']);

        $copyService = $this->createPartialMock(CopyService::class, ['copy']);
        $copyService->expects($this->once())->method('copy')->willReturn($copyService);

        $virtualTheme = $this->createPartialMockWithReflection(
            Theme::class,
            ['createPreviewImageCopy', '__wakeup', 'getThemeImage', 'save']
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
            Collection::class,
            ['addTypeFilter', 'addAreaFilter', 'addFilter', 'count']
        );

        $themeCollection->expects($this->any())->method('addTypeFilter')->willReturn($themeCollection);

        $themeCollection->expects($this->any())->method('addAreaFilter')->willReturn($themeCollection);

        $themeCollection->expects($this->any())->method('addFilter')->willReturn($themeCollection);

        $themeCollection->expects($this->once())->method('count')->willReturn(1);

        $domainModel = new Physical(
            $this->createMock(ThemeInterface::class),
            $themeFactory,
            $copyService,
            $themeCollection
        );
        $domainModel->createVirtualTheme($physicalTheme);
    }
}
