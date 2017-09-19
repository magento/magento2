<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Date
 */
namespace Magento\Framework\Data\Form\Element;

class DateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Data\Form\ElementFactory
     */
    protected $_elementFactory;

    /**
     * SetUp method
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_elementFactory = $objectManager->create(\Magento\Framework\Data\Form\ElementFactory::class);
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetValue(array $data, $expect)
    {
        /** @var $date \Magento\Framework\Data\Form\Element\Date */
        $date = $this->_elementFactory->create(\Magento\Framework\Data\Form\Element\Date::class, $data);
        $this->assertEquals($expect, $date->getValue());
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
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
                $date->format('g:i A')
            ],
            [
                [
                    'date_format' => 'MM/d/yy',
                    'value' => $testTimestamp,
                ],
                $date->format('m/j/y')
            ]
        ];
    }
}
