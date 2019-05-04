<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRuleProcessor;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var \Magento\CatalogRule\Plugin\Indexer\Category
     */
    protected $plugin;

    protected function setUp()
    {
        $this->productRuleProcessor = $this->createMock(
            \Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor::class
        );
        $this->subject = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getChangedProductIds', '__wakeUp']
        );

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
            ->will($this->returnValue([]));

        $this->productRuleProcessor->expects($this->never())
            ->method('reindexList');

        $this->assertEquals($this->subject, $this->plugin->afterSave($this->subject, $this->subject));
    }

    public function testAfterSave()
    {
        $productIds = [1, 2, 3];

        $this->subject->expects($this->any())
            ->method('getChangedProductIds')
            ->will($this->returnValue($productIds));

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
