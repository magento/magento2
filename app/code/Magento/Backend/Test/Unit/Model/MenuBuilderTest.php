<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Setup\MenuBuilder;
use Magento\Framework\App\DocRootLocator;
use PHPUnit\Framework\TestCase;

class MenuBuilderTest extends TestCase
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
        $docRootLocator = $this->createMock(DocRootLocator::class);
        $docRootLocator->expects($this->once())->method('isPub')->willReturn($isPub);
        $model = new MenuBuilder($docRootLocator);
        /** @var Menu $menu */
        $menu = $this->createMock(Menu::class);
        $menu->expects($this->exactly($times))->method('remove')->willReturn(true);

        /** @var Builder $menuBuilder */
        $menuBuilder = $this->createMock(Builder::class);

        $this->assertInstanceOf(
            Menu::class,
            $model->afterGetResult($menuBuilder, $menu)
        );
    }

    /**
     * @return array
     */
    public function afterGetResultDataProvider()
    {
        return [[true, 1], [false, 0]];
    }
}
