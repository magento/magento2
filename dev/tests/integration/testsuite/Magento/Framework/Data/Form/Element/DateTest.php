<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->_localeDate = $objectManager->get('\Magento\Framework\Stdlib\DateTime\Timezone');
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
        return array(
            array(
                array('date_format' => \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
                    'time_format' => \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
                    'value' => '1397832240'
                ),
                '4/18/14 2:44 PM'
            ),
            array(
                array('time_format' => \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
                    'value' => '1397832240'
                ),
                '2:44 PM'
            ),
            array(
                array('date_format' => \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
                    'value' => '1397832240'
                ),
                '4/18/14'
            )
        );
    }
}
