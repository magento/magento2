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
namespace Magento\Framework\Convert;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Convert\Object
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new Object();
    }

    public function testToOptionArray()
    {
        $mockFirst = $this->getMock('Magento\Framework\Object', array('getId', 'getCode'), array());
        $mockFirst->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $mockFirst->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('code1'));
        $mockSecond = $this->getMock('Magento\Framework\Object', array('getId', 'getCode'), array());
        $mockSecond->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));
        $mockSecond->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('code2'));

        $callable = function ($item) {
            return $item->getCode();
        };

        $items = array(
            $mockFirst,
            $mockSecond,
        );
        $result = array(
            array('value' => 1, 'label' => 'code1'),
            array('value' => 2, 'label' => 'code2'),
        );
        $this->assertEquals($result, $this->model->toOptionArray($items, 'id', $callable));
    }

    public function testToOptionHash()
    {
        $mockFirst = $this->getMock('Magento\Framework\Object', array('getSome', 'getId'), array());
        $mockFirst->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(3));
        $mockFirst->expects($this->once())
            ->method('getSome')
            ->will($this->returnValue('code3'));
        $mockSecond = $this->getMock('Magento\Framework\Object', array('getSome', 'getId'), array());
        $mockSecond->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(4));
        $mockSecond->expects($this->once())
            ->method('getSome')
            ->will($this->returnValue('code4'));

        $callable = function ($item) {
            return $item->getId();
        };
        $items = array(
            $mockFirst,
            $mockSecond,
        );
        $result = array(
            3 => 'code3',
            4 => 'code4',
        );

        $this->assertEquals($result, $this->model->toOptionHash($items, $callable, 'some'));
    }

    public function testConvertDataToArray()
    {
        $object = new \stdClass();
        $object->a = array(array(1));
        $mockFirst = $this->getMock('Magento\Framework\Object', array('getData'));
        $mockSecond = $this->getMock('Magento\Framework\Object', array('getData'));

        $mockFirst->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(array(
                'id' => 1,
                'o' => $mockSecond,
            )));

        $mockSecond->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(array(
                'id' => 2,
                'o' => $mockFirst,
            )));

        $data = array(
            'object' => $mockFirst,
            'stdClass' => $object,
            'test' => 'test',
        );
        $result = array(
            'object' => array(
                'id' => 1,
                'o' => array(
                    'id' => 2,
                    'o' => '*** CYCLE DETECTED ***',
                ),
            ),
            'stdClass' => array(
                'a' => array(
                    array(1),
                ),
            ),
            'test' => 'test',
        );
        $this->assertEquals($result, $this->model->convertDataToArray($data));
    }
}
