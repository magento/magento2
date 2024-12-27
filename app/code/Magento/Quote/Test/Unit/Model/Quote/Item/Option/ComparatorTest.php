<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Item\Option;

use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item\Option\Comparator;
use Magento\Quote\Model\Quote\Item\Option\ComparatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test quote item options comparator
 */
class ComparatorTest extends TestCase
{
    /**
     * @var ComparatorInterface|MockObject
     */
    private $customComparator;

    /**
     * @var Comparator
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customComparator = $this->createMock(ComparatorInterface::class);
        $this->model = new Comparator(
            [
                'custom' => $this->customComparator
            ]
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
        $this->customComparator
            ->method('compare')
            ->willReturnCallback(
                function ($option1, $option2) {
                    return $option1->getValue() === $option2->getValue();
                }
            );
        $this->assertEquals($expected, $this->model->compare(new DataObject($option1), new DataObject($option2)));
    }

    /**
     * @return array
     */
    public static function compareDataProvider(): array
    {
        return [
            [
                ['code' => 'test', 'value' => '1'],
                ['code' => 'test', 'value' => '1'],
                true
            ],
            [
                ['code' => 'test', 'value' => '1'],
                ['code' => 'test', 'value' => 1],
                true
            ],
            [
                ['code' => 'test', 'value' => '1'],
                ['code' => 'test', 'value' => '2'],
                false
            ],
            [
                ['code' => 'test', 'value' => '1'],
                ['code' => 'test1', 'value' => '1'],
                false
            ],
            [
                ['code' => 'custom', 'value' => '1'],
                ['code' => 'custom', 'value' => '1'],
                true
            ],
            [
                ['code' => 'custom', 'value' => '1'],
                ['code' => 'custom', 'value' => 1],
                false
            ],
            [
                ['code' => 'custom', 'value' => '1'],
                ['code' => 'custom', 'value' => '2'],
                false
            ],
            [
                ['code' => 'custom', 'value' => '1'],
                ['code' => 'test1', 'value' => '1'],
                false
            ],
        ];
    }
}
