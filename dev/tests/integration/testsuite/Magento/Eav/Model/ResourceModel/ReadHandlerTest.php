<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Eav/_files/attribute_for_search.php
 */
class ReadHandlerTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var \ReflectionMethod
     */
    private $getAttributesMethod;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $metadataPool = $objectManager->create(
            MetadataPool::class,
            [
                'metadata' => [
                    'Test\Entity\Type' => [
                        'entityTableName' => 'test_entity',
                        'eavEntityType' => 'test',
                        'identifierField' => 'entity_id',
                    ]
                ]
            ]
        );
        $this->readHandler = $objectManager->create(ReadHandler::class, ['metadataPool' => $metadataPool]);
        $this->getAttributesMethod = new \ReflectionMethod(
            \Magento\Eav\Model\ResourceModel\ReadHandler::class,
            'getAttributes'
        );
        $this->getAttributesMethod->setAccessible(true);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown entity type: unknown requested
     */
    public function testGetAttributesWithException()
    {
        $this->assertEquals(
            [],
            $this->getAttributesMethod->invoke($this->readHandler, 'unknown')
        );
    }

    public function testGetAttributes()
    {
        $attributes = $this->getAttributesMethod->invoke($this->readHandler, 'Test\Entity\Type');
        $expectedAttributeCodes = [
            'attribute_for_search_1',
            'attribute_for_search_2',
            'attribute_for_search_3',
            'attribute_for_search_4',
            'attribute_for_search_5',
        ];
        $this->assertEquals(count($expectedAttributeCodes), count($attributes));
        $attributeCodes = [];
        foreach ($attributes as $attribute) {
            $attributeCodes[] = $attribute->getAttributeCode();
        }
        $this->assertEquals($expectedAttributeCodes, $attributeCodes);
    }
}
