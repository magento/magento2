<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer;

use Magento\Catalog\Model\Category;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    /**
     * @var ProductRuleProcessor|MockObject
     */
    protected $productRuleProcessor;

    /**
     * @var Category|MockObject
     */
    protected $subject;

    /**
     * @var \Magento\CatalogRule\Plugin\Indexer\Category
     */
    protected $plugin;

    protected function setUp(): void
    {
        $this->productRuleProcessor = $this->createMock(
            ProductRuleProcessor::class
        );
        $this->subject = $this->getMockBuilder(Category::class)
            ->addMethods(['getChangedProductIds'])
            ->onlyMethods(['__wakeUp'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = (new ObjectManager($this))->getObject(
            \Magento\CatalogRule\Plugin\Indexer\Category::class,
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
