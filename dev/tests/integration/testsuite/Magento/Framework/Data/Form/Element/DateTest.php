<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Data\Form\ElementFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests for \Magento\Framework\Data\Form\Element\Date
 */
class DateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ElementFactory
     */
    private $elementFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->elementFactory = $objectManager->create(ElementFactory::class);
    }

    /**
     * Test get value
     *
     * @param array $data
     * @param string $expect
     * @return void
     * @dataProvider getValueDataProvider
     */
    public function testGetValue(array $data, string $expect): void
    {
        /** @var $date Date */
        $date = $this->elementFactory->create(Date::class, $data);
        $this->assertEquals($expect, $date->getValue());
    }

    /**
     * Get value test data provider
     *
     * @return array
     */
    public static function getValueDataProvider(): array
    {
        $stringDates = ['2020-05-18 12:08:16', '1920-10-25 10:10:10', '2122-01-11 10:30:00'];
        $testTimestamps = [strtotime($stringDates[0]), strtotime($stringDates[1]), strtotime($stringDates[2])];
        $dates = [new \DateTime($stringDates[0]),  new \DateTime($stringDates[1]), new \DateTime($stringDates[2])];
        $data = [];
        foreach ($testTimestamps as $key => $testTimestamp) {
            $data[$key] = [
                [
                    [
                        'date_format' => 'MM/d/yy',
                        'time_format' => 'h:mm a',
                        'value' => $testTimestamp,
                    ],
                    $dates[$key]->format('m/j/y g:i A'),
                ],
                [
                    [
                        'time_format' => 'h:mm a',
                        'value' => $testTimestamp,
                    ],
                    $dates[$key]->format('g:i A'),
                ],
                [
                    [
                        'date_format' => 'MM/d/yy',
                        'value' => $testTimestamp,
                    ],
                    $dates[$key]->format('m/j/y'),
                ],
                [
                    [
                        'date_format' => 'd-MM-Y',
                        'value' => $dates[$key]->format('d-m-Y'),
                    ],
                    $dates[$key]->format('d-m-Y'),
                ],
            ];
        }
        return array_merge($data[0], $data[1]);
    }
}
