<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test theme staging model
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Domain;

use Magento\Theme\Model\CopyService;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\Domain\Staging;
use PHPUnit\Framework\TestCase;

class StagingTest extends TestCase
{
    /**
     * @covers \Magento\Theme\Model\Theme\Domain\Staging::__construct
     * @covers \Magento\Theme\Model\Theme\Domain\Staging::updateFromStagingTheme
     */
    public function testUpdateFromStagingTheme()
    {
        $parentTheme = $this->createMock(Theme::class);

        $theme = $this->createPartialMock(Theme::class, ['__wakeup', 'getParentTheme']);
        $theme->expects($this->once())->method('getParentTheme')->willReturn($parentTheme);

        $themeCopyService = $this->createPartialMock(CopyService::class, ['copy']);
        $themeCopyService->expects($this->once())->method('copy')->with($theme, $parentTheme);

        $object = new Staging($theme, $themeCopyService);
        $this->assertSame($object, $object->updateFromStagingTheme());
    }
}
