<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Theme\Model\Theme\StoreThemesResolver;
use Magento\Theme\Model\Theme\StoreThemesResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test store composite themes resolver model.
 */
class StoreThemesResolverTest extends TestCase
{
    /**
     * @var StoreThemesResolverInterface[]|MockObject[]
     */
    private $resolvers;
    /**
     * @var StoreThemesResolver
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->resolvers = [];
        $this->resolvers[] = $this->createMock(StoreThemesResolverInterface::class);
        $this->resolvers[] = $this->createMock(StoreThemesResolverInterface::class);
        $this->resolvers[] = $this->createMock(StoreThemesResolverInterface::class);
        $this->model = new StoreThemesResolver($this->resolvers);
    }

    /**
     * Test that constructor SHOULD throw an exception when resolver is not instance of StoreThemesResolverInterface.
     */
    public function testInvalidConstructorArguments(): void
    {
        $resolver = $this->createMock(StoreInterface::class);
        $this->expectExceptionObject(
            new \InvalidArgumentException(
                sprintf(
                    'Instance of %s is expected, got %s instead.',
                    StoreThemesResolverInterface::class,
                    get_class($resolver)
                )
            )
        );
        $this->model = new StoreThemesResolver(
            [
                $resolver
            ]
        );
    }

    /**
     * Test that method returns aggregated themes from resolvers
     *
     * @param array $themes
     * @param array $expected
     * @dataProvider getThemesDataProvider
     */
    public function testGetThemes(array $themes, array $expected): void
    {
        $store = $this->createMock(StoreInterface::class);
        foreach ($this->resolvers as $key => $resolver) {
            $resolver->expects($this->once())
                ->method('getThemes')
                ->willReturn($themes[$key]);
        }
        $this->assertEquals($expected, $this->model->getThemes($store));
    }

    /**
     * @return array
     */
    public function getThemesDataProvider(): array
    {
        return [
            [
                [
                    [],
                    [],
                    []
                ],
                []
            ],
            [
                [
                    ['1'],
                    [],
                    ['1']
                ],
                ['1']
            ],
            [
                [
                    ['1'],
                    ['2'],
                    ['1']
                ],
                ['1', '2']
            ]
        ];
    }
}
