<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme staging model
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Domain;

class StagingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Theme\Model\Theme\Domain\Staging::__construct
     * @covers \Magento\Theme\Model\Theme\Domain\Staging::updateFromStagingTheme
     */
    public function testUpdateFromStagingTheme()
    {
        $parentTheme = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false, false);

        $theme = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['__wakeup', 'getParentTheme'],
            [],
            '',
            false,
            false
        );
        $theme->expects($this->once())->method('getParentTheme')->will($this->returnValue($parentTheme));

        $themeCopyService = $this->getMock('Magento\Theme\Model\CopyService', ['copy'], [], '', false);
        $themeCopyService->expects($this->once())->method('copy')->with($theme, $parentTheme);

        $object = new \Magento\Theme\Model\Theme\Domain\Staging($theme, $themeCopyService);
        $this->assertSame($object, $object->updateFromStagingTheme());
    }
}
