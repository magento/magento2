<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogImportExport\Model\Import\Product\Validator;
use Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor;

class CategoryProcessorTest extends \PHPUnit_Framework_TestCase
{
    const PARENT_CATEGORY_ID = 1;

    const CHILD_CATEGORY_ID = 2;

    const CHILD_CATEGORY_NAME = 'Child';

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryProcessor;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected $product;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $childCategory = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->getMock();
        $childCategory->method('getId')->will($this->returnValue(self::CHILD_CATEGORY_ID));
        $childCategory->method('getName')->will($this->returnValue('Child'));
        $childCategory->method('getPath')->will($this->returnValue(
            self::PARENT_CATEGORY_ID . CategoryProcessor::DELIMITER_CATEGORY
            . self::CHILD_CATEGORY_ID
        ));

        $parentCategory = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->getMock();
        $parentCategory->method('getId')->will($this->returnValue(self::PARENT_CATEGORY_ID));
        $parentCategory->method('getName')->will($this->returnValue('Parent'));
        $parentCategory->method('getPath')->will($this->returnValue(self::PARENT_CATEGORY_ID));

        $categoryCollection =
            $this->objectManagerHelper->getCollectionMock(
                'Magento\Catalog\Model\ResourceModel\Category\Collection',
                [
                    self::PARENT_CATEGORY_ID => $parentCategory,
                    self::CHILD_CATEGORY_ID => $childCategory,
                ]
            );
        $map = [
            [self::PARENT_CATEGORY_ID, $parentCategory],
            [self::CHILD_CATEGORY_ID, $childCategory],
        ];
        $categoryCollection->expects($this->any())
            ->method('getItemById')
            ->will($this->returnValueMap($map));
        $categoryCollection->expects($this->exactly(3))
            ->method('addAttributeToSelect')
            ->withConsecutive(
                ['name'],
                ['url_key'],
                ['url_path']
            )
            ->will($this->returnSelf());

        $categoryColFactory = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Category\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $categoryColFactory->method('create')->will($this->returnValue($categoryCollection));

        $categoryFactory = $this->getMock(
            'Magento\Catalog\Model\CategoryFactory',
            ['create'],
            [],
            '',
            false
        );

        $categoryFactory->method('create')->will($this->returnValue($childCategory));

        $this->categoryProcessor =
            new \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor(
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

    public function getCategoryByIdDataProvider()
    {
        return [
            [
                '$categoriesCache' => [
                    'category_id' => 'category_id value',
                ],
                '$expectedResult' => 'category_id value',
            ],
            [
                '$categoriesCache' => [],
                '$expectedResult' => null,
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
}
