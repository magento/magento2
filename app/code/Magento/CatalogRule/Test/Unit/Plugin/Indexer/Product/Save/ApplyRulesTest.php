<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer\Product\Save;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ApplyRulesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productRuleProcessor;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subject;

    /**
     * @var \Magento\Framework\Model\AbstractModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $model;

    /**
     * @var \Magento\CatalogRule\Plugin\Indexer\Product\Save\ApplyRules
     */
    private $plugin;

    protected function setUp(): void
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
