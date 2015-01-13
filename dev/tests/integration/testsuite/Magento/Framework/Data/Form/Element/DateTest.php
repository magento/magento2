<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Date
 */
namespace Magento\Framework\Data\Form\Element;

class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Form\ElementFactory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_elementFactory = $objectManager->create('Magento\Framework\Data\Form\ElementFactory');
        $this->_localeDate = $objectManager->get('Magento\Framework\Stdlib\DateTime\Timezone');
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetValue(array $data, $expect)
    {
        if (isset($data['date_format'])) {
            $data['date_format'] = $this->_localeDate->getDateFormat($data['date_format']);
        }
        if (isset($data['time_format'])) {
            $data['time_format'] = $this->_localeDate->getTimeFormat($data['time_format']);
        }
        /** @var $date \Magento\Framework\Data\Form\Element\Date*/
        $date = $this->_elementFactory->create('Magento\Framework\Data\Form\Element\Date', $data);
        $this->assertEquals($expect, $date->getValue());
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        $testTimestamp = strtotime('2014-05-18 12:08:16');
        return [
            [
                [
                    'date_format' => \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
                    'time_format' => \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
                    'value' => $testTimestamp,
                ],
                date('n/j/y g:i A', $testTimestamp),
            ],
            [
                [
                    'time_format' => \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
                    'value' => $testTimestamp,
                ],
                date('g:i A', $testTimestamp)
            ],
            [
                [
                    'date_format' => \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
                    'value' => $testTimestamp,
                ],
                date('n/j/y', $testTimestamp)
            ]
        ];
    }
}
