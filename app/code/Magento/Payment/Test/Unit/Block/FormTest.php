<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Block;

use Magento\Framework\DataObject;

class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_eventManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_escaper;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_storeManager = $this->getMockBuilder(
            \Magento\Store\Model\StoreManager::class
        )->setMethods(
            ['getStore']
        )->disableOriginalConstructor()->getMock();
        $this->_eventManager = $this->getMockBuilder(
            \Magento\Framework\Event\ManagerInterface::class
        )->setMethods(
            ['dispatch']
        )->disableOriginalConstructor()->getMock();
        $this->_escaper = $helper->getObject(\Magento\Framework\Escaper::class);
        $context = $helper->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            [
                'storeManager' => $this->_storeManager,
                'eventManager' => $this->_eventManager,
                'escaper' => $this->_escaper
            ]
        );
        $this->_object = $helper->getObject(\Magento\Payment\Block\Form::class, ['context' => $context]);
    }

    /**
     */
    public function testGetMethodException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $method = new \Magento\Framework\DataObject([]);
        $this->_object->setData('method', $method);
        $this->_object->getMethod();
    }

    public function testGetMethodCode()
    {
        $method = $this->createMock(\Magento\Payment\Model\MethodInterface::class);
        $method->expects($this->once())
            ->method('getCode')
            ->willReturn('method_code');
        $this->_object->setData('method', $method);
        $this->assertEquals('method_code', $this->_object->getMethodCode());
    }

    /**
     * @dataProvider getInfoDataProvider
     */
    public function testGetInfoData($field, $value, $expected)
    {
        $methodInstance = $this->getMockBuilder(\Magento\Payment\Model\Method\AbstractMethod::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $methodInstance->expects($this->any())
            ->method('getData')
            ->with($field)
            ->willReturn($value);
        $method = $this->getMockBuilder(
            \Magento\Payment\Model\MethodInterface::class
        )->getMockForAbstractClass();
        $method->expects($this->any())
            ->method('getInfoInstance')
            ->willReturn($methodInstance);
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

    public function testSetMethod()
    {
        $methodInterfaceMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();

        $this->assertSame($this->_object, $this->_object->setMethod($methodInterfaceMock));
    }
}
