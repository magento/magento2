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
use Magento\Payment\Block\Info;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InfoTest extends TestCase
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
        $this->_object = $helper->getObject(Info::class, ['context' => $context]);
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
            $this->_storeManager->expects($this->any())->method('getStore')->willReturn($storeMock);
        }

        $paymentInfo = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentInfo->expects($this->any())->method('getMethodInstance')->willReturn($methodInstance);

        $this->_object->setData('info', $paymentInfo);
        $this->_object->setData('is_secure_mode', $isSecureMode);
        $result = $this->_object->getIsSecureMode();
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public static function getIsSecureModeDataProvider()
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
        )->onlyMethods(
            ['getStore']
        )->disableOriginalConstructor()
            ->getMock();
        $methodInstance->expects($this->any())->method('getStore')->willReturn($store);
        return $methodInstance;
    }

    /**
     * @param string $storeCode
     * @return MockObject
     */
    protected function _getStoreMock($storeCode)
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->any())->method('getCode')->willReturn($storeCode);
        return $storeMock;
    }

    public function testGetInfoThrowException()
    {
        $this->expectException(LocalizedException::class);
        $this->_object->setData('info', new DataObject([]));
        $this->_object->getInfo();
    }

    public function testGetSpecificInformation()
    {
        $paymentInfo = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_object->setData('info', $paymentInfo);
        $result = $this->_object->getSpecificInformation();
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider getValueAsArrayDataProvider
     */
    public function testGetValueAsArray($value, $escapeHtml, $expected)
    {
        $result = $this->_object->getValueAsArray($value, $escapeHtml);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public static function getValueAsArrayDataProvider()
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
