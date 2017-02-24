<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer\Product\Save;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ApplyRulesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRuleProcessor;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;

    /**
     * @var \Magento\Framework\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\CatalogRule\Plugin\Indexer\Product\Save\ApplyRules
     */
    private $plugin;

    protected function setUp()
    {
        $this->productRuleProcessor = $this
            ->getMockBuilder(\Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            true,
            true,
            ['getIsMassupdate', 'getId']
        );

        $this->plugin = (new ObjectManager($this))->getObject(
            \Magento\CatalogRule\Plugin\Indexer\Product\Save\ApplyRules::class,
            [
                'productRuleProcessor' => $this->productRuleProcessor,
            ]
        );
    }

    public function testAfterSave()
    {
        $this->model->expects($this->once())->method('getIsMassupdate')->willReturn(null);
        $this->model->expects($this->once())->method('getId')->willReturn(1);

        $this->productRuleProcessor->expects($this->once())->method('reindexRow')->willReturnSelf();

        $this->assertSame(
            $this->subject,
            $this->plugin->afterSave($this->subject, $this->subject, $this->model)
        );
    }
}
