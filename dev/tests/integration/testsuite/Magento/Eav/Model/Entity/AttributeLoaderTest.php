<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity;

use Magento\Framework\DataObject;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;

/**
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/category_attribute.php
 */
class AttributeLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeLoader
     */
    private $attributeLoader;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    
    protected function setUp()
    {
        CacheCleaner::cleanAll();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->attributeLoader = $this->objectManager->get(AttributeLoader::class);
    }

    /**
     * @param int $expectedNumOfAttributesByCode
     * @param int $expectedNumOfAttributesByTable
     * @param DataObject|null $object
     * @dataProvider loadAllAttributesDataProvider
     */
    public function testLoadAllAttributesTheFirstTime(
        $expectedNumOfAttributesByCode,
        $expectedNumOfAttributesByTable,
        $object
    ) {
        /** @var \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel */
        $categoryResourceModel = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Category::class);

        // Before load all attributes
        $attributesByCode = $categoryResourceModel->getAttributesByCode();
        $attributesByTable = $categoryResourceModel->getAttributesByTable();
        $this->assertEquals(0, count($attributesByCode));
        $this->assertEquals(0, count($attributesByTable));

        // Load all attributes
        $categoryResourceModel2 = $this->attributeLoader->loadAllAttributes(
            $categoryResourceModel,
            $object
        );
        $attributesByCode2 = $categoryResourceModel2->getAttributesByCode();
        $attributesByTable2 = $categoryResourceModel2->getAttributesByTable();
        $this->assertEquals($expectedNumOfAttributesByCode, count($attributesByCode2));
        $this->assertEquals($expectedNumOfAttributesByTable, count($attributesByTable2));
    }

    public function loadAllAttributesDataProvider()
    {
        return [
            [
                40,
                5,
                null
            ],
            [
                39,
                5,
                new DataObject(
                    [
                        'attribute_set_id' => 3,
                        'store_id' => 0
                    ]
                ),
            ],
            [
                39,
                5,
                new DataObject(
                    [
                        'attribute_set_id' => 3,
                        'store_id' => 10
                    ]
                ),
            ],
        ];
    }
}
