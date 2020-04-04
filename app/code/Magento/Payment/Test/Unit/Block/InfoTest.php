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
use Magento\Payment\Model\Info;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InfoTest extends TestCase
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
        $this->objectMock = $helper->getObject(\Magento\Payment\Block\Info::class, ['context' => $context]);
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
            $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        }

        $paymentInfo = $this->getMockBuilder(Info::class)
            ->disableOriginalConstructor()->getMock();
        $paymentInfo->expects($this->any())->method('getMethodInstance')->will($this->returnValue($methodInstance));

        $this->objectMock->setData('info', $paymentInfo);
        $this->objectMock->setData('is_secure_mode', $isSecureMode);
        $result = $this->objectMock->getIsSecureMode();
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
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
     * @return MockObject
     */
    protected function _getMethodInstanceMock($store)
    {
        $methodInstance = $this->getMockBuilder(
            AbstractMethod::class
        )->setMethods(
            ['getStore']
        )->disableOriginalConstructor()->getMock();
        $methodInstance->expects($this->any())->method('getStore')->will($this->returnValue($store));
        return $methodInstance;
    }

    /**
     * @param string $storeCode
     * @return MockObject
     */
    protected function _getStoreMock($storeCode)
    {
        $storeMock = $this->getMockBuilder(Store::class)->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->any())->method('getCode')->will($this->returnValue($storeCode));
        return $storeMock;
    }

    public function testGetInfoThrowException()
    {
        $this->expectException(LocalizedException::class);
        $this->objectMock->setData('info', new DataObject([]));
        $this->objectMock->getInfo();
    }

    public function testGetSpecificInformation()
    {
        $paymentInfo = $this->getMockBuilder(Info::class)
            ->disableOriginalConstructor()->getMock();

        $this->objectMock->setData('info', $paymentInfo);
        $result = $this->objectMock->getSpecificInformation();
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider getValueAsArrayDataProvider
     */
    public function testGetValueAsArray($value, $escapeHtml, $expected)
    {
        $result = $this->objectMock->getValueAsArray($value, $escapeHtml);
        $this->assertEquals($expected, $result);
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
            [['key' => 'v"a!@#%$%^^&&*(*/\'\]l'], true, ['key' => 'v&quot;a!@#%$%^^&amp;&amp;*(*/&#039;\]l']],
            [['key' => 'val'], false, ['key' => 'val']]
        ];
    }
}
