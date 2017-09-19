<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity;

use Magento\Framework\DataObject;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;

/**
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Eav/_files/attribute_for_search.php
 */
class AttributeLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeLoader
     */
    private $attributeLoader;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Eav\Model\Entity\AbstractEntity
     */
    private $resource;

    protected function setUp()
    {
        CacheCleaner::cleanAll();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->attributeLoader = $this->objectManager->get(AttributeLoader::class);
        $entityType = $this->objectManager->create(\Magento\Eav\Model\Entity\Type::class)
            ->loadByCode('test');
        $context = $this->objectManager->get(\Magento\Eav\Model\Entity\Context::class);
        $this->resource = $this->getMockBuilder(\Magento\Eav\Model\Entity\AbstractEntity::class)
            ->setConstructorArgs([$context])
            ->setMethods(['getEntityType', 'getLinkField'])
            ->getMock();
        $this->resource->method('getEntityType')
            ->willReturn($entityType);
        $this->resource->method('getLinkField')
            ->willReturn('link_field');
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
        // Before load all attributes
        $attributesByCode = $this->resource->getAttributesByCode();
        $attributesByTable = $this->resource->getAttributesByTable();
        $this->assertEquals(0, count($attributesByCode));
        $this->assertEquals(0, count($attributesByTable));

        // Load all attributes
        $resource2 = $this->attributeLoader->loadAllAttributes(
            $this->resource,
            $object
        );
        $attributesByCode2 = $resource2->getAttributesByCode();
        $attributesByTable2 = $resource2->getAttributesByTable();
        $this->assertEquals($expectedNumOfAttributesByCode, count($attributesByCode2));
        $this->assertEquals($expectedNumOfAttributesByTable, count($attributesByTable2));
    }

    public function loadAllAttributesDataProvider()
    {
        /** @var \Magento\Eav\Model\Entity\Type $entityType */
        $entityType = Bootstrap::getObjectManager()->create(\Magento\Eav\Model\Entity\Type::class)
            ->loadByCode('order');
        $attributeSetId = $entityType->getDefaultAttributeSetId();
        return [
            [
                13,
                2,
                null
            ],
            [
                10,
                1,
                new DataObject(
                    [
                        'attribute_set_id' => $attributeSetId,
                        'store_id' => 0
                    ]
                ),
            ],
            [
                10,
                1,
                new DataObject(
                    [
                        'attribute_set_id' => $attributeSetId,
                        'store_id' => 10
                    ]
                ),
            ],
        ];
    }
}
