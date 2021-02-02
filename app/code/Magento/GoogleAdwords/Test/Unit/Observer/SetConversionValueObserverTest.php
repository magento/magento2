<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Test\Unit\Observer;

use Magento\GoogleAdwords\Helper\Data;

class SetConversionValueObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_helperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_collectionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_registryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_eventObserverMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_eventMock;

    /**
     * @var \Magento\GoogleAdwords\Observer\SetConversionValueObserver
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_helperMock = $this->createMock(\Magento\GoogleAdwords\Helper\Data::class);
        $this->_registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->_collectionMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $this->_eventObserverMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->_eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getOrderIds']);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManager->getObject(
            \Magento\GoogleAdwords\Observer\SetConversionValueObserver::class,
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
    public function dataProviderForDisabled()
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
    public function dataProviderForOrdersIds()
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

        $orderMock = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
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
        )->withConsecutive(
            [
                Data::CONVERSION_VALUE_CURRENCY_REGISTRY_NAME,
                $conversionCurrency
            ],
            [
                Data::CONVERSION_VALUE_REGISTRY_NAME,
                $conversionValue,
            ]
        );

        $this->assertSame($this->_model, $this->_model->execute($this->_eventObserverMock));
    }
}
