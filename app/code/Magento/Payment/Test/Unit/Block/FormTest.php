<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Block;

use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $objectMock;

    /**
     * @var StoreManager|MockObject
     */
    private $storeManagerMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->storeManagerMock = $this->getMockBuilder(
            StoreManager::class
        )->setMethods(
            ['getStore']
        )->disableOriginalConstructor()->getMock();
        $this->eventManagerMock = $this->getMockBuilder(
            ManagerInterface::class
        )->setMethods(
            ['dispatch']
        )->disableOriginalConstructor()->getMock();
        $this->escaperMock = $helper->getObject(Escaper::class);
        $context = $helper->getObject(
            Context::class,
            [
                'storeManager' => $this->storeManagerMock,
                'eventManager' => $this->eventManagerMock,
                'escaper' => $this->escaperMock
            ]
        );
        $this->objectMock = $helper->getObject(Form::class, ['context' => $context]);
    }

    public function testGetMethodException()
    {
        $this->expectException(LocalizedException::class);
        $method = new DataObject([]);
        $this->objectMock->setData('method', $method);
        $this->objectMock->getMethod();
    }

    public function testGetMethodCode()
    {
        $method = $this->createMock(MethodInterface::class);
        $method->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('method_code'));
        $this->objectMock->setData('method', $method);
        $this->assertEquals('method_code', $this->objectMock->getMethodCode());
    }

    /**
     * @dataProvider getInfoDataProvider
     */
    public function testGetInfoData($field, $value, $expected)
    {
        $methodInstance = $this->getMockBuilder(AbstractMethod::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $methodInstance->expects($this->any())
            ->method('getData')
            ->with($field)
            ->will($this->returnValue($value));
        $method = $this->getMockBuilder(
            MethodInterface::class
        )->getMockForAbstractClass();
        $method->expects($this->any())
            ->method('getInfoInstance')
            ->will($this->returnValue($methodInstance));
        $this->objectMock->setData('method', $method);
        $this->assertEquals($expected, $this->objectMock->getInfoData($field));
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
        $methodInterfaceMock = $this->getMockBuilder(MethodInterface::class)
            ->getMockForAbstractClass();

        $this->assertSame($this->objectMock, $this->objectMock->setMethod($methodInterfaceMock));
    }
}
