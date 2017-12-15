<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\CategoryLink;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class CategoryLinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CategoryLink
     */
    private $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \Magento\Framework\DB\Select|MockObject
     */
    private $dbSelectMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|MockObject
     */
    private $connectionMock;

    protected function setUp()
    {
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CategoryLink(
            $this->metadataPoolMock,
            $this->resourceMock
        );
    }

    private function prepareAdapter()
    {
        $this->dbSelectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->dbSelectMock);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
    }

    private function prepareMetadata()
    {
        $categoryLinkMetadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $categoryLinkMetadata->expects($this->any())->method('getEntityTable')->willReturn('category_link_table');
        $categoryEntityMetadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $categoryEntityMetadata->expects($this->any())->method('getEntityTable')->willReturn('category_entity_table');
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturnMap(
            [
                [\Magento\Catalog\Api\Data\CategoryLinkInterface::class, $categoryLinkMetadata],
                [\Magento\Catalog\Api\Data\CategoryInterface::class, $categoryEntityMetadata],
            ]
        );
    }

    public function testGetCategoryLinks()
    {
        $this->prepareAdapter();
        $this->prepareMetadata();
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)->getMockForAbstractClass();
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
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)->getMockForAbstractClass();
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

        $actualResult = $this->model->saveCategoryLinks($product, $newCategoryLinks);
        sort($actualResult);
        $this->assertEquals($affectedIds, $actualResult);
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
                [], // Nothing to update - data not changed
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
                [3, 4, 5], // 4 - updated position, 5 - added, 3 - deleted
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
                [3, 4], // 3 - updated position, 4 - deleted
            ],
            [
                [],
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                ],
                [3, 4], // 3, 4 - deleted
            ],
        ];
    }
}
