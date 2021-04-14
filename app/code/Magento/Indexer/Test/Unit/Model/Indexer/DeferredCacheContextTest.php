<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Indexer;

use Magento\Framework\Indexer\CacheContext;
use Magento\Indexer\Model\Indexer\DeferredCacheContext;
use PHPUnit\Framework\TestCase;

/**
 * Test deferred cache context for indexers
 */
class DeferredCacheContextTest extends TestCase
{
    /**
     * @var CacheContext
     */
    private $context;

    /**
     * @var DeferredCacheContext
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->context = new CacheContext();
        $this->model = new DeferredCacheContext($this->context);
    }

    /**
     * Test that deferred cache works correctly
     */
    public function test(): void
    {
        $productTag = 'cat_p';
        $categoryTag = 'cat_c';
        $additionalTags = ['cat_c_p'];
        $productIds = [1, 2, 3];
        $categoryIds = [5, 6, 7];
        $this->model->start();
        $this->model->registerEntities($productTag, $productIds);
        $this->model->start();
        $this->model->registerEntities($categoryTag, $categoryIds);
        $this->model->registerTags($additionalTags);
        $this->assertEmpty($this->context->getIdentities());
        $this->model->commit();
        $this->assertEmpty($this->context->getIdentities());
        $this->model->commit();
        $this->assertEquals(
            ['cat_p_1', 'cat_p_2', 'cat_p_3', 'cat_c_5', 'cat_c_6', 'cat_c_7', 'cat_c_p'],
            $this->context->getIdentities()
        );
    }
}
