<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category\Link;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Category\Link\SaveHandler;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CategoryLink;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveHandlerTest extends TestCase
{
    use MockCreationTrait;
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
        $this->productCategoryLink = $this->createMock(CategoryLink::class);
        $this->hydrator = $this->createMock(HydratorInterface::class);
        $this->hydratorPool = $this->createMock(HydratorPool::class);

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
     */
    #[DataProvider('getCategoryDataProvider')]
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

        $extensionAttributes = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getCategoryLinks']
        );
        $extensionAttributes->expects(static::any())
            ->method('getCategoryLinks')
            ->willReturn($categoryLinks);

        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['getExtensionAttributes', 'getCategoryIds', 'setAffectedCategoryIds', 'setIsChangedCategories']
        );
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
    public static function getCategoryDataProvider(): array
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
        $product = $this->createPartialMock(
            Product::class,
            ['getExtensionAttributes']
        );
        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn(null);

        $entity = $this->saveHandler->execute($product);
        static::assertSame($product, $entity);
    }
}
