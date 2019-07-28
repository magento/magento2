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
    protected function setUp()
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
    public function getValueDataProvider(): array
    {
        $testTimestamp = strtotime('2014-05-18 12:08:16');
        $date = new \DateTime('@' . $testTimestamp);
        return [
            [
                [
                    'date_format' => 'MM/d/yy',
                    'time_format' => 'h:mm a',
                    'value' => $testTimestamp,
                ],
                $date->format('m/j/y g:i A'),
            ],
            [
                [
                    'time_format' => 'h:mm a',
                    'value' => $testTimestamp,
                ],
                $date->format('g:i A'),
            ],
            [
                [
                    'date_format' => 'MM/d/yy',
                    'value' => $testTimestamp,
                ],
                $date->format('m/j/y'),
            ],
            [
                [
                    'date_format' => 'd-MM-Y',
                    'value' => $date->format('d-m-Y'),
                ],
                $date->format('d-m-Y'),
            ],
        ];
    }
}
