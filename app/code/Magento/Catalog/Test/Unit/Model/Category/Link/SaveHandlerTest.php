<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category\Link;

use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Category\Link\SaveHandler;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CategoryLink;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\EntityManager\HydratorPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveHandlerTest extends TestCase
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->productCategoryLink = $this->getMockBuilder(CategoryLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->hydrator = $this->getMockBuilder(HydratorInterface::class)
            ->getMockForAbstractClass();
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
     * @param array|null $categoryLinks
     * @param array $existCategoryLinks
     * @param array $expectedCategoryLinks
     * @param array $affectedIds
     *
     * @return void
     * @dataProvider getCategoryDataProvider
     */
    public function testExecute(
        array $categoryIds,
        ?array $categoryLinks,
        array $existCategoryLinks,
        array $expectedCategoryLinks,
        array $affectedIds
    ): void {
        if ($categoryLinks) {
            $this->hydrator->expects(static::any())
                ->method('extract')
                ->willReturnArgument(0);
            $this->hydratorPool->expects(static::once())
                ->method('getHydrator')
                ->with(CategoryLinkInterface::class)
                ->willReturn($this->hydrator);
        }

        $extensionAttributes = $this->getMockBuilder(ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCategoryLinks', 'getCategoryLinks'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects(static::any())
            ->method('getCategoryLinks')
            ->willReturn($categoryLinks);

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getExtensionAttributes', 'getCategoryIds'])
            ->addMethods(['setAffectedCategoryIds', 'setIsChangedCategories'])
            ->getMock();
        $product->method('setIsChangedCategories')->withConsecutive([false]);
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
    public function getCategoryDataProvider(): array
    {
        return [
            [
                [3, 4, 5], //model category_ids
                null, // dto category links
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20]
                ],
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20],
                    ['category_id' => 5, 'position' => 0]
                ],
                [3,4,5] //affected category_ids
            ],
            [
                [3, 4], //model category_ids
                [], // dto category links
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20]
                ],
                [],
                [3,4] //affected category_ids
            ],
            [
                [], //model category_ids
                [
                    ['category_id' => 3, 'position' => 20]
                ], // dto category links
                [
                    ['category_id' => 3, 'position' => 10],
                    ['category_id' => 4, 'position' => 20]
                ],
                [
                    ['category_id' => 3, 'position' => 20]
                ],
                [3,4] //affected category_ids
            ],
            [
                [3], //model category_ids
                [
                    ['category_id' => 3, 'position' => 20]
                ], // dto category links
                [
                    ['category_id' => 3, 'position' => 10]
                ],
                [
                    ['category_id' => 3, 'position' => 20]
                ],
                [3] //affected category_ids
            ],
            [
                [], //model category_ids
                [
                    ['category_id' => 3, 'position' => 10]
                ], // dto category links
                [
                    ['category_id' => 3, 'position' => 10]
                ],
                [
                    ['category_id' => 3, 'position' => 10]
                ],
                [] //affected category_ids
            ],
            [
                [3], //model category_ids
                [
                    ['category_id' => 3, 'position' => 20],
                    ['category_id' => 4, 'position' => 30]
                ], // dto category links
                [
                    ['category_id' => 3, 'position' => 10]
                ],
                [
                    ['category_id' => 3, 'position' => 20],
                    ['category_id' => 4, 'position' => 30]
                ],
                [3, 4] //affected category_ids
            ]
        ];
    }

    /**
     * @return void
     */
    public function testExecuteWithoutProcess(): void
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getExtensionAttributes'])
            ->addMethods(['hasCategoryIds'])
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
