<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Category\Link;

use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Model\Category\Link\SaveHandler;
use Magento\Catalog\Model\ResourceModel\Product\CategoryLink;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Catalog\Model\Product;
use Magento\Framework\Indexer\IndexerRegistry;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class SaveHandlerTest
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var CategoryLink|MockObject
     */
    private $productCategoryLink;

    /**
     * @var HydratorInterface|MockObject
     */
    private $hydrator;

    /**
     * @var HydratorPool|MockObject
     */
    private $hydratorPool;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productCategoryLink = $this->getMockBuilder(CategoryLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->hydrator = $this->getMockBuilder(HydratorInterface::class)->getMockForAbstractClass();
        $this->hydratorPool = $this->getMockBuilder(HydratorPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->saveHandler = new SaveHandler(
            $this->productCategoryLink,
            $this->hydratorPool
        );
    }

    /**
     * @param array $categoryIds
     * @param array $categoryLinks
     * @param array $existCategoryLinks
     * @param array $expectedCategoryLinks
     * @param array $affectedIds
     *
     * @dataProvider getCategoryDataProvider
     */
    public function testExecute($categoryIds, $categoryLinks, $existCategoryLinks, $expectedCategoryLinks, $affectedIds)
    {
        if ($categoryLinks) {
            $this->hydrator->expects(static::any())
                ->method('extract')
                ->willReturnArgument(0);
            $this->hydratorPool->expects(static::once())
                ->method('getHydrator')
                ->with(CategoryLinkInterface::class)
                ->willReturn($this->hydrator);
        }

        $extensionAttributes = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCategoryLinks', 'getCategoryLinks'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects(static::any())
            ->method('getCategoryLinks')
            ->willReturn($categoryLinks);

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getExtensionAttributes',
                    'setAffectedCategoryIds',
                    'setIsChangedCategories',
                    'getCategoryIds'
                ]
            )
            ->getMock();
        $product->expects(static::at(0))->method('setIsChangedCategories')->with(false);
        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $product->expects(static::any())
            ->method('getCategoryIds')
            ->willReturn($categoryIds);

        $this->productCategoryLink->expects(static::any())
            ->method('saveCategoryLinks')
            ->with($product, $expectedCategoryLinks)
            ->willReturn($affectedIds);

        if (!empty($affectedIds)) {
            $product->expects(static::once())
                ->method('setAffectedCategoryIds')
                ->with($affectedIds);
            $product->expects(static::exactly(2))->method('setIsChangedCategories');
        }

        $this->productCategoryLink->expects(static::any())
            ->method('getCategoryLinks')
            ->with($product, $categoryIds)
            ->willReturn($existCategoryLinks);

        $entity = $this->saveHandler->execute($product);
        static::assertSame($product, $entity);
    }

    /**
     * @return array
     */
    public function getCategoryDataProvider()
    {
        return [
            [
                [3, 4, 5], //model category_ids
                null, // dto category links
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                ],
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                    ['category_id' => 5, 'position' => 0],
                ],
                [3,4,5], //affected category_ids
            ],
            [
                [3, 4], //model category_ids
                [], // dto category links
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                ],
                [],
                [3,4], //affected category_ids
            ],
            [
                [], //model category_ids
                [
                    ['category_id' => 3, 'position' => 20],
                ], // dto category links
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                ],
                [
                    ['category_id' => 3, 'position' => 20],
                ],
                [3,4], //affected category_ids
            ],
            [
                [3], //model category_ids
                [
                    ['category_id' => 3, 'position' => 20],
                ], // dto category links
                [
                    ['category_id' => 3, 'position' => 10],
                ],
                [
                    ['category_id' => 3, 'position' => 20],
                ],
                [3], //affected category_ids
            ],
            [
                [], //model category_ids
                [
                    ['category_id' => 3, 'position' => 10],
                ], // dto category links
                [
                    ['category_id' => 3, 'position' => 10],
                ],
                [
                    ['category_id' => 3, 'position' => 10],
                ],
                [], //affected category_ids
            ],
        ];
    }

    public function testExecuteWithoutProcess()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes', 'hasCategoryIds'])
            ->getMock();
        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn(null);
        $product->expects(static::any())
            ->method('hasCategoryIds')
            ->willReturn(false);

        $entity = $this->saveHandler->execute($product);
        static::assertSame($product, $entity);
    }
}
