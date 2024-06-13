<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Swatches\Block\LayeredNavigation\RenderLayered;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Model\Plugin\FilterRenderer;
use Magento\Swatches\Model\Plugin\FilterRenderer as FilterRendererPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class FilterRendererTest extends TestCase
{
    use MockCreationTrait;
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

        $this->filterMock = $this->createPartialMockWithReflection(
            AbstractFilter::class,
            ['getAttributeModel', 'setAttributeModel', 'hasAttributeModel']
        );
        $attributeModel = null;
        $this->filterMock->method('getAttributeModel')->willReturnCallback(function () use (&$attributeModel) {
            return $attributeModel;
        });
        $this->filterMock->method('setAttributeModel')->willReturnCallback(function ($attr) use (&$attributeModel) {
            $attributeModel = $attr;
        });
        $this->filterMock->method('hasAttributeModel')->willReturnCallback(function () use (&$attributeModel) {
            return $attributeModel !== null;
        });

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
        $this->filterMock->setAttributeModel($attributeMock);
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
        $this->filterMock->setAttributeModel($attributeMock);
        $this->swatchHelperMock
            ->expects($this->once())
            ->method('isSwatchAttribute')
            ->with($attributeMock)
            ->willReturn(false);

        $result = $this->plugin->aroundRender($this->filterRendererMock, $this->closureMock, $this->filterMock);
        $this->assertEquals($result, $this->filterMock);
    }
}
