<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block;

use Magento\Framework\Object;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_escaper;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_storeManager = $this->getMockBuilder(
            '\Magento\Store\Model\StoreManager'
        )->setMethods(
                ['getStore']
            )->disableOriginalConstructor()->getMock();
        $this->_eventManager = $this->getMockBuilder(
            '\Magento\Framework\Event\ManagerInterface'
        )->setMethods(
                ['dispatch']
            )->disableOriginalConstructor()->getMock();
        $this->_escaper = $this->getMock('\Magento\Framework\Escaper', null, [], '', true);
        $context = $helper->getObject(
            'Magento\Framework\View\Element\Template\Context',
            [
                'storeManager' => $this->_storeManager,
                'eventManager' => $this->_eventManager,
                'escaper' => $this->_escaper
            ]
        );
        $this->_object = $helper->getObject('Magento\Payment\Block\Form', ['context' => $context]);
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testGetMethodException()
    {
        $method = new \Magento\Framework\Object([]);
        $this->_object->setData('method', $method);
        $this->_object->getMethod();
    }

    public function testGetMethodCode()
    {
        $method = $this->getMock('Magento\Payment\Model\MethodInterface', [], [], '', false);
        $method->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('method_code'));
        $this->_object->setData('method', $method);
        $this->assertEquals('method_code', $this->_object->getMethodCode());
    }

    /**
     * @dataProvider getInfoDataProvider
     */
    public function testGetInfoData($field, $value, $expected)
    {
        $methodInstance = $this->getMockBuilder('\Magento\Payment\Model\Method\AbstractMethod')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $methodInstance->expects($this->any())
            ->method('getData')
            ->with($field)
            ->will($this->returnValue($value));
        $method = $this->getMock(
            'Magento\Payment\Model\MethodInterface',
            ['getInfoInstance', 'getFormBlockType', 'getTitle', 'getCode'],
            [],
            '',
            false
        );
        $method->expects($this->any())
            ->method('getInfoInstance')
            ->will($this->returnValue($methodInstance));
        $this->_object->setData('method', $method);
        $this->assertEquals($expected, $this->_object->getInfoData($field));
    }

    /**
     * @return array
     */
    public function getInfoDataProvider()
    {
        return [
            ['info', 'blah-blah', 'blah-blah'],
            ['field1', ['key' => 'val'], ['val']],
            [
                'some_field',
                ['aa', '!@#$%^&*()_#$%@^%&$%^*%&^*', 'cc'],
                ['aa', '!@#$%^&amp;*()_#$%@^%&amp;$%^*%&amp;^*', 'cc']
            ]
        ];
    }
}
