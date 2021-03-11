<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme staging model
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Domain;

class StagingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Magento\Theme\Model\Theme\Domain\Staging::__construct
     * @covers \Magento\Theme\Model\Theme\Domain\Staging::updateFromStagingTheme
     */
    public function testUpdateFromStagingTheme()
    {
        $parentTheme = $this->createMock(\Magento\Theme\Model\Theme::class);

        $theme = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['__wakeup', 'getParentTheme']);
        $theme->expects($this->once())->method('getParentTheme')->willReturn($parentTheme);

        $themeCopyService = $this->createPartialMock(\Magento\Theme\Model\CopyService::class, ['copy']);
        $themeCopyService->expects($this->once())->method('copy')->with($theme, $parentTheme);

        $object = new \Magento\Theme\Model\Theme\Domain\Staging($theme, $themeCopyService);
        $this->assertSame($object, $object->updateFromStagingTheme());
    }
}
