<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Test\Unit;

use Magento\Framework\Indexer\CacheContext;
use PHPUnit\Framework\TestCase;

class CacheContextTest extends TestCase
{
    /**
     * @var Batch
     */
    private $object;

    protected function setUp(): void
    {
        $this->object = new CacheContext();
    }

    /**
     * @param array $tagsData
     * @param array $expected
     * @dataProvider getTagsDataProvider
     */
    public function testUniqueTags($tagsData, $expected)
    {
        foreach ($tagsData as $tagSet) {
            foreach ($tagSet as $cacheTag => $ids) {
                $this->object->registerEntities($cacheTag, $ids);
            }
        }

        $this->assertEquals($this->object->getIdentities(), $expected);
    }

    /**
     * @return array
     */
    public static function getTagsDataProvider()
    {
        return [
            'same entities and ids' => [
                [['cat_p' => [1]], ['cat_p' => [1]]],
                ['cat_p_1']
            ],
            'same entities with overlapping ids' => [
                [['cat_p' => [1, 2, 3]], ['cat_p' => [3]]],
                ['cat_p_1', 'cat_p_2', 'cat_p_3']
            ]
        ];
    }
}
