<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
    protected $_object;

    /**
     * @var MockObject
     */
    protected $_storeManager;

    /**
     * @var MockObject
     */
    protected $_eventManager;

    /**
     * @var MockObject
     */
    protected $_escaper;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->_storeManager = $this->getMockBuilder(
            StoreManager::class
        )->onlyMethods(
            ['getStore']
        )->disableOriginalConstructor()
            ->getMock();
        $this->_eventManager = $this->getMockBuilder(
            ManagerInterface::class
        )->onlyMethods(
            ['dispatch']
        )->disableOriginalConstructor()
            ->getMock();
        $this->_escaper = $helper->getObject(Escaper::class);
        $context = $helper->getObject(
            Context::class,
            [
                'storeManager' => $this->_storeManager,
                'eventManager' => $this->_eventManager,
                'escaper' => $this->_escaper
            ]
        );
        $this->_object = $helper->getObject(Form::class, ['context' => $context]);
    }

    public function testGetMethodException()
    {
        $this->expectException(LocalizedException::class);
        $method = new DataObject([]);
        $this->_object->setData('method', $method);
        $this->_object->getMethod();
    }

    public function testGetMethodCode()
    {
        $method = $this->getMockForAbstractClass(MethodInterface::class);
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
        $methodInstance = $this->getMockBuilder(AbstractMethod::class)
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $methodInstance->expects($this->any())
            ->method('getData')
            ->with($field)
            ->willReturn($value);
        $method = $this->getMockBuilder(
            MethodInterface::class
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
    public static function getInfoDataProvider()
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

        $this->assertSame($this->_object, $this->_object->setMethod($methodInterfaceMock));
    }
}
