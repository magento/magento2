<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme domain physical model
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Domain;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\CopyService;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\Domain\Physical;
use PHPUnit\Framework\TestCase;

class PhysicalTest extends TestCase
{
    public function testCreateVirtualTheme()
    {
        $physicalTheme = $this->createPartialMock(Theme::class, ['__wakeup']);
        $physicalTheme->setData(['parent_id' => 10, 'theme_title' => 'Test Theme']);

        $copyService = $this->createPartialMock(CopyService::class, ['copy']);
        $copyService->expects($this->once())->method('copy')->will($this->returnValue($copyService));

        $virtualTheme = $this->createPartialMock(
            Theme::class,
            ['__wakeup', 'getThemeImage', 'createPreviewImageCopy', 'save']
        );
        $virtualTheme->expects($this->once())->method('getThemeImage')->will($this->returnValue($virtualTheme));

        $virtualTheme->expects(
            $this->once()
        )->method(
            'createPreviewImageCopy'
        )->will(
            $this->returnValue($virtualTheme)
        );

        $virtualTheme->expects($this->once())->method('save')->will($this->returnValue($virtualTheme));

        $themeFactory = $this->createPartialMock(\Magento\Theme\Model\ThemeFactory::class, ['create']);
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($virtualTheme));

        $themeCollection = $this->createPartialMock(
            Collection::class,
            ['addTypeFilter', 'addAreaFilter', 'addFilter', 'count']
        );

        $themeCollection->expects($this->any())->method('addTypeFilter')->will($this->returnValue($themeCollection));

        $themeCollection->expects($this->any())->method('addAreaFilter')->will($this->returnValue($themeCollection));

        $themeCollection->expects($this->any())->method('addFilter')->will($this->returnValue($themeCollection));

        $themeCollection->expects($this->once())->method('count')->will($this->returnValue(1));

        $domainModel = new Physical(
            $this->createMock(ThemeInterface::class),
            $themeFactory,
            $copyService,
            $themeCollection
        );
        $domainModel->createVirtualTheme($physicalTheme);
    }
}
