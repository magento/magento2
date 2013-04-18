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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Formatter_JsonTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webhook_Model_Formatter_Json
     */
    protected $_formatter;

    public function setup()
    {
        $this->_formatter = $this->getMock(
            'Mage_Webhook_Model_Formatter_Json', array(
                                                    'newMessage',
                                               )
        );
        $this->_formatter->expects($this->once())->method('newMessage')->will(
            $this->returnValue(
                $this->getMockBuilder('Mage_Webhook_Model_Message')
                        ->setMethods(array('_construct')) // all other methods need to work as normal
                        ->disableOriginalConstructor()
                        ->getMock()));
    }

    public function beforeAfterJsonFormat()
    {
        return array(
            array(array(), "[]"),
            array(
                array('a' => array('b' => 'c', 'd' => 'e'), 'f' => 'g'), "{\"a\":{\"b\":\"c\",\"d\":\"e\"},\"f\":\"g\"}"
            ),
            array(array(0 => new Data('public', 'protected')), "[{\"dataA\":\"public\"}]"),
            array(array(0 => null), "[null]")
        );
    }

    /**
     * @dataProvider beforeAfterJsonFormat
     * @param $input
     * @param $expectedOutput
     */
    public function testFormat($input, $expectedOutput)
    {
        $event = $this->getMock(
            'Mage_Webhook_Model_Event_Interface', array(
                                                       'getStatus', 'getMapping', 'getHeaders',
                                                       'getBodyData', 'getOptions', 'getTopic'
                                                  )
        );
        $event->expects($this->once())->method('getMapping')->will($this->returnValue('mapping'));
        $event->expects($this->once())->method('getHeaders')->will($this->returnValue(array()));
        $event->expects($this->once())->method('getBodyData')->will($this->returnValue($input));
        $message = $this->_formatter->format($event);
        $this->assertEquals($expectedOutput, $message->getBody());
        $headers = $message->getHeaders();
        $this->assertCount(1, $headers);
        $this->assertContains(
            Mage_Webhook_Model_Formatter_Interface::CONTENT_TYPE_HEADER,
            array_keys($headers)
        );
        $this->assertEquals(
            Mage_Webhook_Model_Formatter_Json::CONTENT_TYPE,
            $headers[Mage_Webhook_Model_Formatter_Interface::CONTENT_TYPE_HEADER]
        );
        $this->assertEquals('mapping', $message->getMapping());
    }
}


class Data
{
    public $dataA;
    protected $_dataB;

    public function getB()
    {
        return $this->_dataB;
    }

    public function __construct($first, $second)
    {
        $this->dataA  = $first;
        $this->_dataB = $second;
    }
}

;
