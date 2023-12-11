<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CategoryLink;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryLinkTest extends TestCase
{
    /**
     * @var CategoryLink
     */
    private $model;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var Select|MockObject
     */
    private $dbSelectMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    protected function setUp(): void
    {
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CategoryLink(
            $this->metadataPoolMock,
            $this->resourceMock
        );
    }

    private function prepareAdapter()
    {
        $this->dbSelectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->dbSelectMock);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
    }

    private function prepareMetadata()
    {
        $categoryLinkMetadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $categoryLinkMetadata->expects($this->any())->method('getEntityTable')->willReturn('category_link_table');
        $categoryEntityMetadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $categoryEntityMetadata->expects($this->any())->method('getEntityTable')->willReturn('category_entity_table');
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturnMap(
            [
                [CategoryLinkInterface::class, $categoryLinkMetadata],
                [CategoryInterface::class, $categoryEntityMetadata],
            ]
        );
    }

    public function testGetCategoryLinks()
    {
        $this->prepareAdapter();
        $this->prepareMetadata();
        $product = $this->getMockBuilder(ProductInterface::class)
            ->getMockForAbstractClass();
        $product->expects($this->any())->method('getId')->willReturn(1);
        $this->connectionMock->expects($this->once())->method('fetchAll')->with($this->dbSelectMock)->willReturn(
            [
                ['category_id' => 3, 'position' => 10],
                ['category_id' => 4, 'position' => 20],
            ]
        );

        $this->assertEquals(
            [
                ['category_id' => 3, 'position' => 10],
                ['category_id' => 4, 'position' => 20],
            ],
            $this->model->getCategoryLinks($product, [3, 4])
        );
    }

    /**
     * @param array $newCategoryLinks
     * @param array $dbCategoryLinks
     * @param array $affectedIds
     * @dataProvider getCategoryLinksDataProvider
     */
    public function testSaveCategoryLinks($newCategoryLinks, $dbCategoryLinks, $affectedIds)
    {
        $this->prepareAdapter();
        $this->prepareMetadata();
        $product = $this->getMockBuilder(ProductInterface::class)
            ->getMockForAbstractClass();
        $product->expects($this->any())->method('getId')->willReturn(1);
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->dbSelectMock)
            ->willReturn($dbCategoryLinks);
        if (!empty($newCategoryLinks)) {
            $this->connectionMock->expects($this->once())
                ->method('fetchCol')
                ->with($this->dbSelectMock)
                ->willReturn(
                    array_intersect(
                        [3, 4, 5], // valid category_ids
                        array_column($newCategoryLinks, 'category_id')
                    )
                );
        }

        $expectedResult = [];

        foreach ($affectedIds as $type => $ids) {
            $expectedResult[] = $ids;
            // Verify if the correct insert, update and/or delete actions are performed:
            $this->setupExpectationsForConnection($type, $ids);
        }
        $expectedResult = array_merge([], ...$expectedResult);

        $actualResult = $this->model->saveCategoryLinks($product, $newCategoryLinks);

        sort($actualResult);
        sort($expectedResult);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Category links data provider
     *
     * @return array
     */
    public function getCategoryLinksDataProvider()
    {
        return [
            [
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                ],
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                ],
                [
                    'update' => [],
                    'insert' => [],
                    'delete' => [],
                ],
            ],
            [
                [
                    ['category_id' => 4, 'position' => 30],
                    ['category_id' => 5, 'position' => 40],
                ],
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                ],
                [
                    'update' => [4],
                    'insert' => [5],
                    'delete' => [3],
                ],
            ],
            [
                [
                    ['category_id' => 6, 'position' => 30], //6 - not valid category,
                    ['category_id' => 3, 'position' => 40],
                ],
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                ],
                [
                    'update' => [3],
                    'insert' => [],
                    'delete' => [4],
                ],
            ],
            [
                [],
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                ],
                [
                    'update' => [],
                    'insert' => [],
                    'delete' => [3, 4],
                ],
            ],
            [
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                ],
                [
                    ['category_id' => 3, 'position' => 20], // swapped positions
                    ['category_id' => 4, 'position' => 10], // swapped positions
                ],
                [
                    'update' => [3, 4],
                    'insert' => [],
                    'delete' => [],
                ],
            ]
        ];
    }

    /**
     * @param $type
     * @param $ids
     */
    private function setupExpectationsForConnection($type, $ids): void
    {
        switch ($type) {
            case 'insert':
                $this->connectionMock
                    ->expects($this->exactly(empty($ids) ? 0 : 1))
                    ->method('insertArray')
                    ->with(
                        $this->anything(),
                        $this->anything(),
                        $this->callback(function ($data) use ($ids) {
                            $foundIds = [];
                            foreach ($data as $row) {
                                $foundIds[] = $row['category_id'];
                            }
                            return $ids === $foundIds;
                        })
                    );
                break;
            case 'update':
                $this->connectionMock
                    ->expects($this->exactly(empty($ids) ? 0 : 1))
                    ->method('insertOnDuplicate')
                    ->with(
                        $this->anything(),
                        $this->callback(function ($data) use ($ids) {
                            $foundIds = [];
                            foreach ($data as $row) {
                                $foundIds[] = $row['category_id'];
                            }
                            return $ids === $foundIds;
                        })
                    );
                break;
            case 'delete':
                $this->connectionMock
                    ->expects($this->exactly(empty($ids) ? 0 : 1))
                    ->method('delete')
                    // Verify that the correct category ID's are touched:
                    ->with(
                        $this->anything(),
                        $this->callback(function ($data) use ($ids) {
                            return array_values($data)[1] === $ids;
                        })
                    );
                break;
        }
    }
}
