<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Model;

use Magento\Backend\Model\Setup\MenuBuilder;

class MenuBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider afterGetResultDataProvider
     *
     * @param string $isPub
     * @param int $times
     * @param bool $result
     */
    public function testAfterGetResult($isPub, $times)
    {
        $docRootLocator = $this->getMock(\Magento\Framework\App\DocRootLocator::class, [], [], '', false);
        $docRootLocator->expects($this->once())->method('isPub')->willReturn($isPub);
        $model = new MenuBuilder($docRootLocator);
        /** @var \Magento\Backend\Model\Menu $menu */
        $menu = $this->getMock(\Magento\Backend\Model\Menu::class, [], [], '', false);
        $menu->expects($this->exactly($times))->method('remove')->willReturn(true);

        /** @var \Magento\Backend\Model\Menu\Builder $menuBuilder */
        $menuBuilder = $this->getMock(\Magento\Backend\Model\Menu\Builder::class, [], [], '', false);

        $this->assertInstanceOf(
            \Magento\Backend\Model\Menu::class,
            $model->afterGetResult($menuBuilder, $menu)
        );
    }

    public function afterGetResultDataProvider()
    {
        return [[true, 1], [false, 0],];
    }
}
