<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Model;

use Magento\Backend\Model\Setup\MenuBuilder;

class MenuBuilderTest extends \PHPUnit\Framework\TestCase
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
        $docRootLocator = $this->createMock(\Magento\Framework\App\DocRootLocator::class);
        $docRootLocator->expects($this->once())->method('isPub')->willReturn($isPub);
        $model = new MenuBuilder($docRootLocator);
        /** @var \Magento\Backend\Model\Menu $menu */
        $menu = $this->createMock(\Magento\Backend\Model\Menu::class);
        $menu->expects($this->exactly($times))->method('remove')->willReturn(true);

        /** @var \Magento\Backend\Model\Menu\Builder $menuBuilder */
        $menuBuilder = $this->createMock(\Magento\Backend\Model\Menu\Builder::class);

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
