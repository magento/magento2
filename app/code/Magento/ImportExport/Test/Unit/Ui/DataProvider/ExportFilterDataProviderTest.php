<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Ui\DataProvider;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\ExportFactory;
use Magento\ImportExport\Ui\DataProvider\ExportFilterDataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExportFilterDataProviderTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $collection;

    /**
     * @var MockObject
     */
    private $export;

    /**
     * @var MockObject
     */
    private $request;

    /**
     * @var ExportFilterDataProvider
     */
    private $dataProvider;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);
        $collectionFactory = $this->createConfiguredMock(CollectionFactory::class, [
            'create' => $this->collection
        ]);

        $this->export = $this->createMock(Export::class);
        $exportFactory = $this->createConfiguredMock(ExportFactory::class, [
            'create' => $this->export
        ]);

        $this->request = $this->createMock(RequestInterface::class);

        $objectManager = new ObjectManager($this);
        $this->dataProvider = $objectManager->getObject(ExportFilterDataProvider::class, [
            'collectionFactory' => $collectionFactory,
            'exportFactory' => $exportFactory,
            'request' => $this->request,
            'name' => 'testName',
            'primaryFieldName' => 'testPrimaryFieldName',
            'requestFieldName' => 'testRequestFieldName'
        ]);
    }

    /**
     * @param string $entity
     * @param string $expected
     * @dataProvider getCollectionDataProvider
     */
    public function testGetCollection(string $entity, string $expected)
    {
        $abstractCollection = $this->createMock(AbstractCollection::class);
        $abstractCollection->method('clear')->willReturnSelf();

        $this->request->method('getParam')->with('entity')->willReturn($entity);
        $this->export->method('setData')->willReturnSelf();
        $this->export->method('getEntityAttributeCollection')->willReturn($abstractCollection);
        $this->collection->method('setEntityTypeFilter')->willReturnSelf();

        $this->assertInstanceOf($expected, $this->dataProvider->getCollection());
    }

    /**
     * @return array
     */
    public function getCollectionDataProvider() :array
    {
        return [
            [
                '',
                Collection::class
            ],
            [
                'catalog_product',
                AbstractCollection::class
            ]
        ];
    }
}
