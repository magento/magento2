<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer\Product\Save;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\CatalogRule\Plugin\Indexer\Product\Save\ApplyRulesAfterReindex;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplyRulesAfterReindexTest extends TestCase
{
    /**
     * @var ApplyRulesAfterReindex
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ProductRuleProcessor|MockObject
     */
    private $productRuleProcessorMock;

    /**
     * @var Product|MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->productRuleProcessorMock = $this->createMock(ProductRuleProcessor::class);
        $this->subjectMock = $this->createMock(Product::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            ApplyRulesAfterReindex::class,
            ['productRuleProcessor' => $this->productRuleProcessorMock]
        );
    }

    public function testAfterReindex()
    {
        $id = 'test_id';

        $this->subjectMock->expects(static::any())
            ->method('getId')
            ->willReturn($id);
        $this->productRuleProcessorMock->expects(static::once())
            ->method('reindexRow')
            ->with($id, false);

        $this->plugin->afterReindex($this->subjectMock);
    }
}
