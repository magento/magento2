<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer;

use Magento\Catalog\Model\Category;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\CatalogRule\Plugin\Indexer\Category as CategoryPlugin;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ProductRuleProcessor|MockObject
     */
    protected $productRuleProcessor;

    /**
     * @var Category|MockObject
     */
    protected $subject;

    /**
     * @var CategoryPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        $this->productRuleProcessor = $this->createMock(
            ProductRuleProcessor::class
        );
        $this->subject = $this->createPartialMockWithReflection(
            Category::class,
            ['getChangedProductIds', '__wakeUp']
        );

        $this->plugin = (new ObjectManager($this))->getObject(
            CategoryPlugin::class,
            [
                'productRuleProcessor' => $this->productRuleProcessor,
            ]
        );
    }

    public function testAfterSaveWithoutAffectedProductIds()
    {
        $this->subject->expects($this->any())
            ->method('getChangedProductIds')
            ->willReturn([]);

        $this->productRuleProcessor->expects($this->never())
            ->method('reindexList');

        $this->assertEquals($this->subject, $this->plugin->afterSave($this->subject, $this->subject));
    }

    public function testAfterSave()
    {
        $productIds = [1, 2, 3];

        $this->subject->expects($this->any())
            ->method('getChangedProductIds')
            ->willReturn($productIds);

        $this->productRuleProcessor->expects($this->once())
            ->method('reindexList')
            ->with($productIds);

        $this->assertEquals($this->subject, $this->plugin->afterSave($this->subject, $this->subject));
    }

    public function testAfterDelete()
    {
        $this->productRuleProcessor->expects($this->once())
            ->method('markIndexerAsInvalid');

        $this->assertEquals($this->subject, $this->plugin->afterDelete($this->subject, $this->subject));
    }
}
