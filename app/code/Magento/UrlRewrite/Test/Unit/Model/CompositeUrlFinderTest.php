<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\CompositeUrlFinder;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeUrlFinderTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var MergeDataProviderFactory|MockObject
     */
    private $mergeDataProviderFactory;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $config;

    /**
     * @var UrlFinderInterface|MockObject
     */
    private $defaultFinder;

    /**
     * @var UrlFinderInterface|MockObject
     */
    private $catalogFinder;

    /**
     * @var array
     */
    private $children;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->mergeDataProviderFactory = $this->createMock(MergeDataProviderFactory::class);
        $this->config = $this->createMock(ScopeConfigInterface::class);
        $this->defaultFinder = $this->createMock(UrlFinderInterface::class);
        $this->catalogFinder = $this->createMock(UrlFinderInterface::class);

        $this->children = [
            'catalog' => [
                'class' => 'Magento\CatalogUrlRewrite\Model\Storage\DbStorage',
                'sortOrder' => 20,
            ],
            'default' => [
                'class' => 'Magento\UrlRewrite\Model\Storage\DbStorage',
                'sortOrder' => 10,
            ],
        ];

        $this->objectManager->method('get')
            ->willReturnMap(
                [
                    ['Magento\UrlRewrite\Model\Storage\DbStorage', $this->defaultFinder],
                    ['Magento\CatalogUrlRewrite\Model\Storage\DbStorage', $this->catalogFinder],
                ]
            );
    }

    private function createFinder(): CompositeUrlFinder
    {
        return (new ObjectManager($this))->getObject(
            CompositeUrlFinder::class,
            [
                'children' => $this->children,
                'objectManager' => $this->objectManager,
                'mergeDataProviderFactory' => $this->mergeDataProviderFactory,
                'config' => $this->config,
            ]
        );
    }

    public function testFindOneByDataResolvesEachChildFinderOnlyOnce(): void
    {
        $this->objectManager->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['Magento\UrlRewrite\Model\Storage\DbStorage', $this->defaultFinder],
                    ['Magento\CatalogUrlRewrite\Model\Storage\DbStorage', $this->catalogFinder],
                ]
            );

        $this->defaultFinder->method('findOneByData')->willReturn(null);
        $this->catalogFinder->method('findOneByData')->willReturn(null);

        $finder = $this->createFinder();
        $finder->findOneByData(['request_path' => 'first-lookup']);
        $finder->findOneByData(['request_path' => 'second-lookup']);
    }

    public function testFindOneByDataQueriesChildrenInSortOrderAndReturnsFirstMatch(): void
    {
        $rewrite = $this->createMock(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class);

        $callOrder = [];
        $this->defaultFinder->method('findOneByData')
            ->willReturnCallback(function () use (&$callOrder, $rewrite) {
                $callOrder[] = 'default';
                return $rewrite;
            });
        $this->catalogFinder->method('findOneByData')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'catalog';
                return null;
            });

        $finder = $this->createFinder();
        $result = $finder->findOneByData(['request_path' => 'test']);

        $this->assertSame($rewrite, $result);
        $this->assertSame(['default'], $callOrder);
    }

    public function testFindAllByDataReturnsFirstFinderResultWhenCategoryRewritesEnabled(): void
    {
        $this->config->method('getValue')
            ->with('catalog/seo/generate_category_product_rewrites')
            ->willReturn(true);

        $expected = [$this->createMock(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)];
        $this->defaultFinder->method('findAllByData')->willReturn($expected);
        $this->catalogFinder->expects($this->never())->method('findAllByData');

        $this->mergeDataProviderFactory->method('create')
            ->willReturn($this->createMock(MergeDataProvider::class));

        $finder = $this->createFinder();
        $result = $finder->findAllByData(['request_path' => 'test']);

        $this->assertSame($expected, $result);
    }

    public function testFindAllByDataMergesAllFindersWhenCategoryRewritesDisabled(): void
    {
        $this->config->method('getValue')
            ->with('catalog/seo/generate_category_product_rewrites')
            ->willReturn(false);

        $defaultRewrites = [$this->createMock(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)];
        $catalogRewrites = [$this->createMock(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)];
        $this->defaultFinder->method('findAllByData')->willReturn($defaultRewrites);
        $this->catalogFinder->method('findAllByData')->willReturn($catalogRewrites);

        $mergeDataProvider = $this->createMock(MergeDataProvider::class);
        $this->mergeDataProviderFactory->method('create')->willReturn($mergeDataProvider);

        $mergeCallOrder = [];
        $mergeDataProvider->method('merge')
            ->willReturnCallback(function ($rewrites) use (&$mergeCallOrder, $defaultRewrites, $catalogRewrites) {
                $mergeCallOrder[] = $rewrites === $defaultRewrites ? 'default' : ($rewrites === $catalogRewrites
                    ? 'catalog'
                    : 'unknown');
            });
        $merged = ['merged-result'];
        $mergeDataProvider->method('getData')->willReturn($merged);

        $finder = $this->createFinder();
        $result = $finder->findAllByData(['request_path' => 'test']);

        $this->assertSame(['default', 'catalog'], $mergeCallOrder);
        $this->assertSame($merged, $result);
    }
}
