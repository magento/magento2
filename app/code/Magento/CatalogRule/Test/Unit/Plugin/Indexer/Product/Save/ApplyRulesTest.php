<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
        $this->productRuleProcessor = $this->createMock(ProductRuleProcessor::class);

        $this->subject = $this->createMock(Product::class);

        $this->model = $this->createPartialMock(AbstractModel::class, []);

        $this->plugin = (new ObjectManager($this))->getObject(
            ApplyRules::class,
            [
                'productRuleProcessor' => $this->productRuleProcessor,
            ]
        );
    }

    public function testAfterSave()
    {
        $this->model->setIsMassupdate(null);
        $this->model->setId(1);

        $this->productRuleProcessor->expects($this->once())->method('reindexRow')->willReturnSelf();

        $this->assertSame(
            $this->subject,
            $this->plugin->afterSave($this->subject, $this->subject, $this->model)
        );
    }
}
