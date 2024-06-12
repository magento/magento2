<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleAdwords\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleAdwords\Helper\Data;
use Magento\GoogleAdwords\Observer\SetConversionValueObserver;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetConversionValueObserverTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_helperMock;

    /**
     * @var MockObject
     */
    protected $_collectionMock;

    /**
     * @var MockObject
     */
    protected $_registryMock;

    /**
     * @var MockObject
     */
    protected $_eventObserverMock;

    /**
     * @var MockObject
     */
    protected $_eventMock;

    /**
     * @var SetConversionValueObserver
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_helperMock = $this->createMock(Data::class);
        $this->_registryMock = $this->createMock(Registry::class);
        $this->_collectionMock = $this->createMock(Collection::class);
        $this->_eventObserverMock = $this->createMock(Observer::class);
        $this->_eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getOrderIds'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(
            SetConversionValueObserver::class,
            [
                'helper' => $this->_helperMock,
                'collection' => $this->_collectionMock,
                'registry' => $this->_registryMock
            ]
        );
    }

    /**
     * @return array
     */
    public static function dataProviderForDisabled()
    {
        return [[false, false], [false, true], [true, false]];
    }

    /**
     * @param bool $isActive
     * @param bool $isDynamic
     * @dataProvider dataProviderForDisabled
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSetConversionValueWhenAdwordsDisabled($isActive, $isDynamic)
    {
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleAdwordsActive'
        )->willReturn(
            $isActive
        );
        $this->_helperMock->expects($this->any())->method('isDynamicConversionValue')->willReturnCallback(
            function () use ($isDynamic) {
                return $isDynamic;
            }
        );

        $this->_eventMock->expects($this->never())->method('getOrderIds');
        $this->assertSame($this->_model, $this->_model->execute($this->_eventObserverMock));
    }

    /**
     * @return array
     */
    public static function dataProviderForOrdersIds()
    {
        return [[[]], ['']];
    }

    /**
     * @param $ordersIds
     * @dataProvider dataProviderForOrdersIds
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSetConversionValueWhenAdwordsActiveWithoutOrdersIds($ordersIds)
    {
        $this->_helperMock->expects($this->once())->method('isGoogleAdwordsActive')->willReturn(true);
        $this->_helperMock->expects($this->once())->method('isDynamicConversionValue')->willReturn(true);
        $this->_eventMock->expects($this->once())->method('getOrderIds')->willReturn($ordersIds);
        $this->_eventObserverMock->expects(
            $this->once()
        )->method(
            'getEvent'
        )->willReturn(
            $this->_eventMock
        );
        $this->_collectionMock->expects($this->never())->method('addFieldToFilter');

        $this->assertSame($this->_model, $this->_model->execute($this->_eventObserverMock));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSetConversionValueWhenAdwordsActiveWithOrdersIds()
    {
        $ordersIds = [1, 2, 3];
        $conversionValue = 0;
        $conversionCurrency = 'USD';
        $this->_helperMock->expects($this->once())->method('isGoogleAdwordsActive')->willReturn(true);
        $this->_helperMock->expects($this->once())->method('isDynamicConversionValue')->willReturn(true);
        $this->_helperMock->expects($this->once())->method('hasSendConversionValueCurrency')
            ->willReturn(true);
        $this->_eventMock->expects($this->once())->method('getOrderIds')->willReturn($ordersIds);
        $this->_eventObserverMock->expects(
            $this->once()
        )->method(
            'getEvent'
        )->willReturn(
            $this->_eventMock
        );

        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $orderMock->expects($this->once())->method('getOrderCurrencyCode')->willReturn($conversionCurrency);

        $iteratorMock = new \ArrayIterator([$orderMock]);
        $this->_collectionMock->expects($this->any())->method('getIterator')->willReturn($iteratorMock);
        $this->_collectionMock->expects(
            $this->once()
        )->method(
            'addFieldToFilter'
        )->with(
            'entity_id',
            ['in' => $ordersIds]
        );
        $this->_registryMock->expects(
            $this->atLeastOnce()
        )->method(
            'register'
        ) ->willReturnCallback(
            function ($arg1, $arg2) use ($conversionCurrency, $conversionValue) {
                if ($arg1 === Data::CONVERSION_VALUE_CURRENCY_REGISTRY_NAME && $arg2 == $conversionCurrency) {
                    return null;
                } elseif ($arg1 === Data::CONVERSION_VALUE_REGISTRY_NAME && $arg2 == $conversionValue) {
                    return null;
                }
            }
        );

        $this->assertSame($this->_model, $this->_model->execute($this->_eventObserverMock));
    }
}
