<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Swatches\Block\LayeredNavigation\RenderLayered;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Model\Plugin\FilterRenderer;
use Magento\Swatches\Model\Plugin\FilterRenderer as FilterRendererPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterRendererTest extends TestCase
{
    /** @var FilterRenderer|ObjectManager */
    protected $plugin;

    /** @var MockObject|Data */
    protected $swatchHelperMock;

    /** @var MockObject|Layout */
    protected $layoutMock;

    /** @var MockObject|AbstractFilter */
    protected $filterMock;

    /** @var MockObject|\Magento\LayeredNavigation\Block\Navigation\FilterRenderer */
    protected $filterRendererMock;

    /** @var MockObject|RenderLayered */
    protected $blockMock;

    /** @var MockObject */
    protected $closureMock;

    protected function setUp(): void
    {
        $this->layoutMock = $this->createPartialMock(Layout::class, ['createBlock']);

        $this->swatchHelperMock = $this->createPartialMock(Data::class, ['isSwatchAttribute']);

        $this->blockMock = $this->createPartialMock(
            RenderLayered::class,
            ['setSwatchFilter', 'toHtml']
        );

        $this->filterMock = $this->getMockBuilder(AbstractFilter::class)
            ->addMethods(['hasAttributeModel'])
            ->onlyMethods(['getAttributeModel'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->filterRendererMock = $this->createMock(
            \Magento\LayeredNavigation\Block\Navigation\FilterRenderer::class
        );

        $this->closureMock = function () {
            return $this->filterMock;
        };

        $objectManager = new ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            FilterRendererPlugin::class,
            [
                'layout' => $this->layoutMock,
                'swatchHelper' => $this->swatchHelperMock
            ]
        );
    }

    public function testAroundRenderTrue()
    {
        $attributeMock = $this->createMock(Attribute::class);
        $this->filterMock->expects($this->atLeastOnce())->method('getAttributeModel')->willReturn($attributeMock);
        $this->filterMock->expects($this->once())->method('hasAttributeModel')->willReturn(true);
        $this->swatchHelperMock
            ->expects($this->once())
            ->method('isSwatchAttribute')
            ->with($attributeMock)
            ->willReturn(true);

        $this->layoutMock->expects($this->once())->method('createBlock')->willReturn($this->blockMock);
        $this->blockMock->expects($this->once())->method('setSwatchFilter')->willReturnSelf();

        $this->plugin->aroundRender($this->filterRendererMock, $this->closureMock, $this->filterMock);
    }

    public function testAroundRenderFalse()
    {
        $attributeMock = $this->createMock(Attribute::class);
        $this->filterMock->expects($this->atLeastOnce())->method('getAttributeModel')->willReturn($attributeMock);
        $this->filterMock->expects($this->once())->method('hasAttributeModel')->willReturn(true);
        $this->swatchHelperMock
            ->expects($this->once())
            ->method('isSwatchAttribute')
            ->with($attributeMock)
            ->willReturn(false);

        $result = $this->plugin->aroundRender($this->filterRendererMock, $this->closureMock, $this->filterMock);
        $this->assertEquals($result, $this->filterMock);
    }
}
