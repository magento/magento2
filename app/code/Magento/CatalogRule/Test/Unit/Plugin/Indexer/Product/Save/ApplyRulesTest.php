<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer\Product\Save;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\CatalogRule\Plugin\Indexer\Product\Save\ApplyRules;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplyRulesTest extends TestCase
{
    /**
     * @var ProductRuleProcessor|MockObject
     */
    private $productRuleProcessor;

    /**
     * @var Product|MockObject
     */
    private $subject;

    /**
     * @var AbstractModel|MockObject
     */
    private $model;

    /**
     * @var ApplyRules
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->productRuleProcessor = $this
            ->getMockBuilder(ProductRuleProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->getMockForAbstractClass(
            AbstractModel::class,
            [],
            '',
            false,
            true,
            true,
            ['getIsMassupdate', 'getId']
        );

        $this->plugin = (new ObjectManager($this))->getObject(
            ApplyRules::class,
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
