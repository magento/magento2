<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use ArrayIterator;
use Magento\Framework\App\Area;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;
use Magento\Theme\Model\Theme\StoreDefaultThemeResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test store default theme resolver.
 */
class StoreDefaultThemeResolverTest extends TestCase
{
    /**
     * @var DesignInterface|MockObject
     */
    private $design;
    /**
     * @var StoreDefaultThemeResolver
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $themeCollectionFactory = $this->createMock(CollectionFactory::class);
        $this->design = $this->getMockForAbstractClass(DesignInterface::class);
        $this->model = new StoreDefaultThemeResolver(
            $themeCollectionFactory,
            $this->design
        );
        $registeredThemes = [];
        $registeredThemes[] = $this->createConfiguredMock(
            ThemeInterface::class,
            [
                'getId' => 1,
                'getCode' => 'Magento/luma',
            ]
        );
        $registeredThemes[] = $this->createConfiguredMock(
            ThemeInterface::class,
            [
                'getId' => 2,
                'getCode' => 'Magento/blank',
            ]
        );
        $collection = $this->createMock(Collection::class);
        $collection->method('getIterator')
            ->willReturn(new ArrayIterator($registeredThemes));
        $collection->method('loadRegisteredThemes')
            ->willReturnSelf();
        $themeCollectionFactory->method('create')
            ->willReturn($collection);
    }

    /**
     * Test that method returns default theme associated to given store.
     *
     * @param string|null $defaultTheme
     * @param array $expected
     * @dataProvider getThemesDataProvider
     */
    public function testGetThemes(?string $defaultTheme, array $expected)
    {
        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $this->design->expects($this->once())
            ->method('getConfigurationDesignTheme')
            ->with(
                Area::AREA_FRONTEND,
                ['store' => $store]
            )
            ->willReturn($defaultTheme);
        $this->assertEquals($expected, $this->model->getThemes($store));
    }

    /**
     * @return array
     */
    public function getThemesDataProvider(): array
    {
        return [
            [
                null,
                []
            ],
            [
                '1',
                [1]
            ],
            [
                'Magento/blank',
                [2]
            ],
            [
                'Magento/theme',
                []
            ]
        ];
    }
}
