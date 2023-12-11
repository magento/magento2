<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model;

use Magento\Framework\Indexer\CacheContext;
use PHPUnit\Framework\TestCase;

/**
 * Test indexer cache context
 */
class CacheContextTest extends TestCase
{
    /**
     * @var CacheContext
     */
    protected $context;

    /**
     * Set up test
     */
    protected function setUp(): void
    {
        $this->context = new CacheContext();
    }

    /**
     * Test registerEntities
     */
    public function testRegisterEntities()
    {
        $cacheTag = 'tag';
        $expectedIds = [1, 2, 3];
        $this->context->registerEntities($cacheTag, $expectedIds);
        $actualIds = $this->context->getRegisteredEntity($cacheTag);
        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * Test getIdentities
     *
     * @param array $entities
     * @param array $tags
     * @param array $expected
     * @dataProvider getIdentitiesDataProvider
     */
    public function testGetIdentities(array $entities, array $tags = [], array $expected = []): void
    {
        foreach ($entities as $entity => $ids) {
            $this->context->registerEntities($entity, $ids);
        }
        $this->context->registerTags($tags);
        $this->assertEquals($expected, $this->context->getIdentities());
    }

    /**
     * Test that flush() clears all data
     */
    public function testFlush(): void
    {
        $productTag = 'cat_p';
        $categoryTag = 'cat_c';
        $additionalTags = ['cat_c_p'];
        $productIds = [1, 2, 3];
        $categoryIds = [5, 6, 7];
        $this->context->registerEntities($productTag, $productIds);
        $this->context->registerEntities($categoryTag, $categoryIds);
        $this->context->registerTags($additionalTags);
        $this->assertNotEmpty($this->context->getIdentities());
        $this->context->flush();
        $this->assertEmpty($this->context->getIdentities());
    }

    /**
     * @return array[]
     */
    public function getIdentitiesDataProvider(): array
    {
        return [
            'should return entities and tags' => [
                [
                    'cat_p' => [1, 2, 3],
                    'cat_c' => [5, 6, 7]
                ],
                ['cat_c_p1', 'cat_c_p2'],
                ['cat_p_1', 'cat_p_2', 'cat_p_3', 'cat_c_5', 'cat_c_6', 'cat_c_7', 'cat_c_p1', 'cat_c_p2']
            ],
            'should return unique values' => [
                [
                    'cat_p' => [1, 2, 3, 1, 3],
                    'cat_c' => [5, 6, 7, 6]
                ],
                ['cat_c_p1', 'cat_c_p2'],
                ['cat_p_1', 'cat_p_2', 'cat_p_3', 'cat_c_5', 'cat_c_6', 'cat_c_7', 'cat_c_p1', 'cat_c_p2']
            ]
        ];
    }
}
