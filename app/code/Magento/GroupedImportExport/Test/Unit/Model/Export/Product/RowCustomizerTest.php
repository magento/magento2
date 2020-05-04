<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedImportExport\Test\Unit\Model\Export\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\GroupedImportExport\Model\Export\RowCustomizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RowCustomizerTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var RowCustomizer|MockObject
     */
    protected $rowCustomizerMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rowCustomizerMock = $this->objectManagerHelper->getObject(
            RowCustomizer::class
        );
    }

    /**
     * Test addHeaderColumns()
     */
    public function testAddHeaderColumns()
    {
        $productData = [0 => 'sku'];
        $expectedData = [
            0 => 'sku',
            1 => 'associated_skus'
        ];
        $this->assertEquals($expectedData, $this->rowCustomizerMock->addHeaderColumns($productData));
    }
}
