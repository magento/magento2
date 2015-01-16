<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Plugin\Indexer;

use Magento\TestFramework\Helper\ObjectManager;

class CategoryTest extends \PHPUnit_Framework_TestCase
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
        $this->productRuleProcessor = $this->getMock('Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor',
            [], [], '', false);
        $this->subject = $this->getMock('Magento\Catalog\Model\Category', ['getAffectedProductIds', '__wakeUp'], [],
            '', false);

        $this->plugin = (new ObjectManager($this))->getObject('Magento\CatalogRule\Plugin\Indexer\Category', [
            'productRuleProcessor' => $this->productRuleProcessor,
        ]);
    }

    public function testAfterSaveWithoutAffectedProductIds()
    {
        $this->subject->expects($this->any())->method('getAffectedProductIds')->will($this->returnValue([]));
        $this->productRuleProcessor->expects($this->never())->method('reindexList');

        $this->assertEquals($this->subject, $this->plugin->afterSave($this->subject, $this->subject));
    }

    public function testAfterSave()
    {
        $productIds = [1, 2, 3];

        $this->subject->expects($this->any())->method('getAffectedProductIds')->will($this->returnValue($productIds));
        $this->productRuleProcessor->expects($this->once())->method('reindexList')->with($productIds);

        $this->assertEquals($this->subject, $this->plugin->afterSave($this->subject, $this->subject));
    }

    public function testAfterDelete()
    {
        $this->productRuleProcessor->expects($this->once())->method('markIndexerAsInvalid');

        $this->assertEquals($this->subject, $this->plugin->afterDelete($this->subject, $this->subject));
    }
}
