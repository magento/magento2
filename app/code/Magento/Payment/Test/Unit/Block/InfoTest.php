<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Block;

use Magento\Framework\DataObject;

class InfoTest extends \PHPUnit_Framework_TestCase
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
        $this->_escaper = $this->getMock(\Magento\Framework\Escaper::class, null, [], '', true);
        $context = $helper->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            [
                'storeManager' => $this->_storeManager,
                'eventManager' => $this->_eventManager,
                'escaper' => $this->_escaper
            ]
        );
        $this->_object = $helper->getObject(\Magento\Payment\Block\Info::class, ['context' => $context]);
    }

    /**
     * @dataProvider getIsSecureModeDataProvider
     * @param bool $isSecureMode
     * @param bool $methodInstance
     * @param bool $store
     * @param string $storeCode
     * @param bool $expectedResult
     */
    public function testGetIsSecureMode($isSecureMode, $methodInstance, $store, $storeCode, $expectedResult)
    {
        if (isset($store)) {
            $methodInstance = $this->_getMethodInstanceMock($store);
        }

        if (isset($storeCode)) {
            $storeMock = $this->_getStoreMock($storeCode);
            $this->_storeManager->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        }

        $paymentInfo = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->disableOriginalConstructor()->getMock();
        $paymentInfo->expects($this->any())->method('getMethodInstance')->will($this->returnValue($methodInstance));

        $this->_object->setData('info', $paymentInfo);
        $this->_object->setData('is_secure_mode', $isSecureMode);
        $result = $this->_object->getIsSecureMode();
        $this->assertEquals($result, $expectedResult);
    }

    public function getIsSecureModeDataProvider()
    {
        return [
            [false, true, null, null, false],
            [true, true, null, null, true],
            [null, false, null, null, true],
            [null, null, false, null, false],
            [null, null, true, 'default', true],
            [null, null, true, 'admin', false]
        ];
    }

    /**
     * @param bool $store
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMethodInstanceMock($store)
    {
        $methodInstance = $this->getMockBuilder(
            \Magento\Payment\Model\Method\AbstractMethod::class
        )->setMethods(
            ['getStore']
        )->disableOriginalConstructor()->getMock();
        $methodInstance->expects($this->any())->method('getStore')->will($this->returnValue($store));
        return $methodInstance;
    }

    /**
     * @param string $storeCode
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getStoreMock($storeCode)
    {
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->any())->method('getCode')->will($this->returnValue($storeCode));
        return $storeMock;
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetInfoThrowException()
    {
        $this->_object->setData('info', new \Magento\Framework\DataObject([]));
        $this->_object->getInfo();
    }

    public function testGetSpecificInformation()
    {
        $paymentInfo = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->disableOriginalConstructor()->getMock();

        $this->_object->setData('info', $paymentInfo);
        $this->_object->getSpecificInformation();
    }

    /**
     * @dataProvider getValueAsArrayDataProvider
     */
    public function testGetValueAsArray($value, $escapeHtml, $expected)
    {
        $this->assertEquals($expected, $this->_object->getValueAsArray($value, $escapeHtml));
    }

    /**
     * @return array
     */
    public function getValueAsArrayDataProvider()
    {
        return [
            [[], true, []],
            [[], false, []],
            ['string', true, [0 => 'string']],
            ['string', false, ['string']],
            [['key' => 'v"a!@#%$%^^&&*(*/\'\]l'], true, ['key' => 'v&quot;a!@#%$%^^&amp;&amp;*(*/\'\]l']],
            [['key' => 'val'], false, ['key' => 'val']]
        ];
    }
}
