<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Quote\Item\Option;

use Magento\Bundle\Model\Quote\Item\Option\BundleSelectionAttributesComparator;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

/**
 * Test bundle quote item option comparator
 */
class BundleSelectionAttributesComparatorTest extends TestCase
{
    /**
     * @var BundleSelectionAttributesComparator
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new BundleSelectionAttributesComparator(
            new Json()
        );
    }

    /**
     * @param array $option1
     * @param array $option2
     * @param bool $expected
     * @dataProvider compareDataProvider
     */
    public function testCompare(array $option1, array $option2, bool $expected): void
    {
        $this->assertEquals($expected, $this->model->compare(new DataObject($option1), new DataObject($option2)));
    }

    /**
     * @return array
     */
    public static function compareDataProvider(): array
    {
        return [
            [
                ['code' => 'test', 'value' => '{"option_id":1,"option_label":"Option 1"}'],
                ['code' => 'test', 'value' => '{"option_id":1,"option_label":"Option One"}'],
                true
            ],
            [
                ['code' => 'test', 'value' => '{"option_id":1,"option_label":"Option 1"}'],
                ['code' => 'test', 'value' => '{"option_id":2,"option_label":"Option 1"}'],
                false
            ],
            [
                ['code' => 'test', 'value' => '{"option_id":1,"option_label":"Option 1"}'],
                ['code' => 'test', 'value' => '{"option_label":"Option 1"}'],
                false
            ],
        ];
    }
}
