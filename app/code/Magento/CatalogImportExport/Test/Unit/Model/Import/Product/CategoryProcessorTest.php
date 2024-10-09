<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryProcessorTest extends TestCase
{
    public const PARENT_CATEGORY_ID = 1;

    public const CHILD_CATEGORY_ID = 2;

    public const CHILD_CATEGORY_NAME = 'Child';

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var CategoryProcessor|MockObject
     */
    protected $categoryProcessor;

    /**
     * @var AbstractType
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $childCategory;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $parentCategory;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->childCategory = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->childCategory->method('getId')->willReturn(self::CHILD_CATEGORY_ID);
        $this->childCategory->method('getName')->willReturn(self::CHILD_CATEGORY_NAME);
        $this->childCategory->method('getPath')->willReturn(
            self::PARENT_CATEGORY_ID . CategoryProcessor::DELIMITER_CATEGORY
            . self::CHILD_CATEGORY_ID
        );

        $this->parentCategory = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->parentCategory->method('getId')->willReturn(self::PARENT_CATEGORY_ID);
        $this->parentCategory->method('getName')->willReturn('Parent');
        $this->parentCategory->method('getPath')->willReturn(self::PARENT_CATEGORY_ID);

        $categoryCollection =
            $this->objectManagerHelper->getCollectionMock(
                Collection::class,
                [
                    self::PARENT_CATEGORY_ID => $this->parentCategory,
                    self::CHILD_CATEGORY_ID => $this->childCategory,
                ]
            );
        $map = [
            [self::PARENT_CATEGORY_ID, $this->parentCategory],
            [self::CHILD_CATEGORY_ID, $this->childCategory],
        ];
        $categoryCollection->expects($this->any())
            ->method('getItemById')
            ->willReturnMap($map);
        $categoryCollection->expects($this->exactly(3))
            ->method('addAttributeToSelect')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['name'] => $categoryCollection,
                ['url_key'] => $categoryCollection,
                ['url_path'] => $categoryCollection
            });

        $categoryColFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class,
            ['create']
        );

        $categoryColFactory->method('create')->willReturn($categoryCollection);

        $categoryFactory = $this->createPartialMock(\Magento\Catalog\Model\CategoryFactory::class, ['create']);

        $categoryFactory->method('create')->willReturn($this->childCategory);

        $this->categoryProcessor =
            new CategoryProcessor(
                $categoryColFactory,
                $categoryFactory
            );
    }

    public function testUpsertCategories()
    {
        $categoriesSeparator = ',';
        $categoryIds = $this->categoryProcessor->upsertCategories(self::CHILD_CATEGORY_NAME, $categoriesSeparator);
        $this->assertArrayHasKey(self::CHILD_CATEGORY_ID, array_flip($categoryIds));
    }

    /**
     * Tests case when newly created category save throws exception.
     */
    public function testUpsertCategoriesWithAlreadyExistsException()
    {
        $exception = new AlreadyExistsException();
        $categoriesSeparator = '/';
        $categoryName = 'Exception Category';
        $this->childCategory->method('save')->willThrowException($exception);
        $this->categoryProcessor->upsertCategories($categoryName, $categoriesSeparator);
        $this->assertNotEmpty($this->categoryProcessor->getFailedCategories());
    }

    public function testClearFailedCategories()
    {
        $dummyFailedCategory = [
            [
                'category' => 'dummy category',
                'exception' => 'dummy exception',
            ]
        ];

        $this->setPropertyValue($this->categoryProcessor, 'failedCategories', $dummyFailedCategory);
        $this->assertCount(count($dummyFailedCategory), $this->categoryProcessor->getFailedCategories());

        $this->categoryProcessor->clearFailedCategories();
        $this->assertEmpty($this->categoryProcessor->getFailedCategories());
    }

    /**
     * Cover getCategoryById().
     *
     * @dataProvider getCategoryByIdDataProvider
     */
    public function testGetCategoryById($categoriesCache, $expectedResult)
    {
        $categoryId = 'category_id';
        $this->setPropertyValue($this->categoryProcessor, 'categoriesCache', $categoriesCache);

        $actualResult = $this->categoryProcessor->getCategoryById($categoryId);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public static function getCategoryByIdDataProvider()
    {
        return [
            [
                'categoriesCache' => [
                    'category_id' => 'category_id value',
                ],
                'expectedResult' => 'category_id value',
            ],
            [
                'categoriesCache' => [],
                'expectedResult' => null,
            ],
        ];
    }

    /**
     * Set property for an object.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }

    /**
     * @throws \ReflectionException
     */
    public function testCategoriesCreatedForGlobalScope()
    {
        $this->childCategory->expects($this->once())
            ->method('setStoreId')
            ->with(Store::DEFAULT_STORE_ID);

        $reflection = new \ReflectionClass($this->categoryProcessor);
        $createCategoryReflection = $reflection->getMethod('createCategory');
        $createCategoryReflection->setAccessible(true);
        $createCategoryReflection->invokeArgs($this->categoryProcessor, ['testCategory', 2]);
    }
}
