<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Category;

use Magento\CatalogGraphQl\Model\Resolver\Category\Breadcrumbs;
use Magento\CatalogGraphQl\Model\Resolver\Category\DataProvider\Breadcrumbs as BreadcrumbsDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BreadcrumbsTest extends TestCase
{
    /**
     * @var BreadcrumbsDataProvider|MockObject
     */
    private $dataProvider;

    /**
     * @var Breadcrumbs
     */
    private Breadcrumbs $resolver;

    /**
     * @var Field
     */
    private $field;

    /**
     * @var ResolveInfo
     */
    private $info;

    protected function setUp(): void
    {
        $this->dataProvider = $this->createMock(BreadcrumbsDataProvider::class);
        $valueFactory = $this->createStub(ValueFactory::class);
        $valueFactory->method('create')->willReturnArgument(0);
        $this->field = $this->createStub(Field::class);
        $this->info = $this->createStub(ResolveInfo::class);
        $this->resolver = new Breadcrumbs($this->dataProvider, $valueFactory);
    }

    public function testPendingPathsArePrefetchedTogetherAndEachCategoryUsesGetData(): void
    {
        $item = ['category_id' => 10];
        $this->dataProvider->expects($this->once())
            ->method('prefetch')
            ->with(['1/2/10/11', '1/2/10/12', '1/2/13']);
        $this->dataProvider->expects($this->exactly(3))
            ->method('getData')
            ->willReturnMap([['1/2/10/11', [$item]], ['1/2/10/12', [$item]], ['1/2/13', []]]);

        $first = $this->resolve('1/2/10/11');
        $second = $this->resolve('1/2/10/12');
        $third = $this->resolve('1/2/13');

        $this->assertSame([$item], $first());
        $this->assertSame([$item], $second());
        $this->assertNull($third());
    }

    public function testResetStateDropsPendingPaths(): void
    {
        $this->dataProvider->expects($this->once())->method('prefetch')->with(['1/2/10/12']);
        $this->dataProvider->expects($this->once())->method('getData')->with('1/2/10/12')->willReturn([]);

        $this->resolve('1/2/10/11');
        $this->resolver->_resetState();
        $this->assertNull($this->resolve('1/2/10/12')());
    }

    public function testPathIsRequired(): void
    {
        $this->dataProvider->expects($this->never())->method('prefetch');
        $this->dataProvider->expects($this->never())->method('getData');
        $this->expectException(LocalizedException::class);
        $this->resolver->resolve($this->field, null, $this->info, []);
    }

    /**
     * Resolve breadcrumbs of a category with the given path
     *
     * @param string $path
     * @return callable
     */
    private function resolve(string $path): callable
    {
        return $this->resolver->resolve($this->field, null, $this->info, ['path' => $path]);
    }
}
