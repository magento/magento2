<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Ui\DataProvider\Product;

use Magento\CatalogSearch\Model\ResourceModel\Search\Collection as SearchCollection;
use Magento\CatalogSearch\Ui\DataProvider\Product\AddFulltextFilterToCollection;
use Magento\Framework\Data\Collection;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddFulltextFilterToCollectionTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var SearchCollection|MockObject
     */
    private $searchCollection;

    /**
     * @var Collection|MockObject
     */
    private $collection;

    /**
     * @var AddFulltextFilterToCollection
     */
    private $model;

    protected function setUp(): void
    {
        $this->searchCollection = $this->createPartialMock(
            SearchCollection::class,
            ['addBackendSearchFilter', 'load', 'getAllIds']
        );
        $this->searchCollection->method('load')->willReturnSelf();

        $this->collection = $this->createPartialMockWithReflection(
            Collection::class,
            ['addIdFilter']
        );

        $this->model = new AddFulltextFilterToCollection($this->searchCollection);
    }

    public function testAddFilter()
    {
        $this->searchCollection->expects($this->once())
            ->method('addBackendSearchFilter')
            ->with('test');
        $this->searchCollection->expects($this->once())
            ->method('getAllIds')
            ->willReturn([]);
        $this->collection->expects($this->once())
            ->method('addIdFilter')
            ->with(-1);
        $this->model->addFilter($this->collection, 'test', ['fulltext' => 'test']);
    }
}
