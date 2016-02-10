<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model\SearchEngine;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Builder;
use Magento\Framework\Search\SearchEngine\ConfigInterface;
use Magento\Search\Model\EngineResolver;
use Magento\Backend\Model\Menu\Config as MenuConfig;

class MenuBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MenuConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $menuConfig;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchFeatureConfig;

    /**
     * @var EngineResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $engineResolver;

    protected function setUp()
    {
        $this->menuConfig = $this->getMock('\Magento\Backend\Model\Menu\Config', [], [], '', false);
        $this->searchFeatureConfig = $this->getMock('\Magento\Search\Model\SearchEngine\Config', [], [], '', false);
        $this->engineResolver = $this->getMock('\Magento\Search\Model\EngineResolver', [], [], '', false);
    }

    public function testAfterGetResult()
    {
        $this->engineResolver->expects($this->once())->method('getCurrentSearchEngine')->willReturn('mysql');
        $this->searchFeatureConfig
            ->expects($this->once())
            ->method('isFeatureSupported')
            ->with('synonyms', 'mysql')
            ->willReturn(false);
        /** @var \Magento\Backend\Model\Menu $menu */
        $menu = $this->getMock('\Magento\Backend\Model\Menu', [], [], '', false);
        $menu->expects($this->once())->method('remove')->willReturn(true);

        /** @var \Magento\Backend\Model\Menu\Builder $menuBuilder */
        $menuBuilder = $this->getMock('\Magento\Backend\Model\Menu\Builder', [], [], '', false);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Search\Model\SearchEngine\MenuBuilder $searchMenuBuilder */
        $searchMenuBuilder = $objectManager->getObject(
            'Magento\Search\Model\SearchEngine\MenuBuilder',
            [
                'menuConfig' => $this->menuConfig,
                'searchFeatureConfig' => $this->searchFeatureConfig,
                'engineResolver' => $this->engineResolver
            ]
        );
        $this->assertInstanceOf(
            '\Magento\Backend\Model\Menu',
            $searchMenuBuilder->afterGetResult($menuBuilder, $menu)
        );
    }
}
