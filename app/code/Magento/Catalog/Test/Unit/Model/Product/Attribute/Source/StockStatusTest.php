<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

class StockStatusTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Model\Product\Attribute\Source\StockStatus */
    protected $stockStatus;

    protected function setUp()
    {
        parent::setUp();

        $this->stockStatus = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product\Attribute\Source\StockStatus::class
        );
    }

    public function testGetOptionArray()
    {
        $this->assertEquals([1 => 'In Stock', 0 => 'Out of Stock'], $this->stastockStatus->getOptionArray());
    }

    /**
     * @dataProvider getOptionTextDataProvider
     * @param string $text
     * @param string $id
     */
    public function testGetOptionText($text, $id)
    {
        $this->assertEquals($text, $this->stockStatus->getOptionText($id));
    }

    /**
     * @return array
     */
    public function getOptionTextDataProvider()
    {
        return [
            [
                'text' => 'In Stock',
                'id' => '1',
            ],
            [
                'text' => 'Out of Stock',
                'id' => '0'
            ]
        ];
    }
}
