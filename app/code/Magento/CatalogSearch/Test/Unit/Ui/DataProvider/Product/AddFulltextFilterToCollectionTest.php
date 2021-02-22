<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Ui\DataProvider\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogSearch\Model\ResourceModel\Search\Collection as SearchCollection;
use Magento\Framework\Data\Collection;
use Magento\CatalogSearch\Ui\DataProvider\Product\AddFulltextFilterToCollection;

class AddFulltextFilterToCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SearchCollection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCollection;

    /**
     * @var Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collection;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @var AddFulltextFilterToCollection
     */
    private $model;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);

        $this->searchCollection = $this->getMockBuilder(SearchCollection::class)
            ->setMethods(['addBackendSearchFilter', 'load', 'getAllIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCollection->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->collection = $this->getMockBuilder(Collection::class)
            ->setMethods(['addIdFilter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            AddFulltextFilterToCollection::class,
            [
                'searchCollection' => $this->searchCollection
            ]
        );
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
