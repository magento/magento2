<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

use Magento\Framework\DataObject;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Eav/_files/attribute_for_search.php
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $config;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->config = $objectManager->get(Config::class);
    }

    public function testGetEntityAttributeCodes()
    {
        $entityType = 'test';
        CacheCleaner::cleanAll();
        $entityAttributeCodes1 = $this->config->getEntityAttributeCodes($entityType);
        $this->assertEquals(
            [
                'attribute_for_search_1',
                'attribute_for_search_2',
                'attribute_for_search_3',
                'attribute_for_search_4',
                'attribute_for_search_5',
            ],
            $entityAttributeCodes1
        );

        $entityAttributeCodes2 = $this->config->getEntityAttributeCodes($entityType);
        $this->assertEquals($entityAttributeCodes1, $entityAttributeCodes2);
    }

    public function testGetEntityAttributeCodesWithObject()
    {
        $entityType = 'test';
        /** @var \Magento\Eav\Model\Entity\Type $testEntityType */
        $testEntityType = Bootstrap::getObjectManager()->create(\Magento\Eav\Model\Entity\Type::class)
            ->loadByCode('test');
        $attributeSetId = $testEntityType->getDefaultAttributeSetId();
        CacheCleaner::cleanAll();
        $object = new DataObject(
            [
                'attribute_set_id' => $attributeSetId,
                'store_id' => 0
            ]
        );
        $entityAttributeCodes1 = $this->config->getEntityAttributeCodes($entityType, $object);
        $this->assertEquals(
            [
                'attribute_for_search_1',
                'attribute_for_search_2',
            ],
            $entityAttributeCodes1
        );

        $entityAttributeCodes2 = $this->config->getEntityAttributeCodes($entityType, $object);
        $this->assertEquals($entityAttributeCodes1, $entityAttributeCodes2);
    }

    public function testGetAttributes()
    {
        $entityType = 'test';
        CacheCleaner::cleanAll();
        $attributes1 = $this->config->getAttributes($entityType);
        $expectedAttributeCodes = [
            'attribute_for_search_1',
            'attribute_for_search_2',
            'attribute_for_search_3',
            'attribute_for_search_4',
            'attribute_for_search_5',
        ];
        $this->assertEquals(count($expectedAttributeCodes), count($attributes1));
        $attributeCodes = [];
        foreach ($attributes1 as $attribute) {
            $attributeCodes[] = $attribute->getAttributeCode();
        }
        $this->assertEquals($expectedAttributeCodes, $attributeCodes);
        $attributes2 = $this->config->getAttributes($entityType);
        $this->assertEquals($attributes1, $attributes2);
    }

    public function testGetAttribute()
    {
        $entityType = 'test';
        CacheCleaner::cleanAll();
        $attribute1 = $this->config->getAttribute($entityType, 'attribute_for_search_1');
        $this->assertEquals('attribute_for_search_1', $attribute1->getAttributeCode());
        $this->assertEquals('varchar', $attribute1->getBackendType());
        $this->assertEquals(1, $attribute1->getIsRequired());
        $this->assertEquals(1, $attribute1->getIsUserDefined());
        $this->assertEquals(0, $attribute1->getIsUnique());
        $attribute2 = $this->config->getAttribute($entityType, 'attribute_for_search_1');
        $this->assertEquals($attribute1, $attribute2);
    }
}
