<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Cron;

use \Magento\Sales\Cron\CleanExpiredQuotes;

/**
 * Tests Magento\Sales\Cron\CleanExpiredQuotes
 */
class CleanExpiredQuotesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoresConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storesConfigMock;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactoryMock;

    /**
     * @var \Magento\Sales\Cron\CleanExpiredQuotes
     */
    protected $observer;

    protected function setUp()
    {
        $this->storesConfigMock = $this->getMock('Magento\Store\Model\StoresConfig', [], [], '', false);

        $this->quoteFactoryMock = $this->getMockBuilder('Magento\Quote\Model\ResourceModel\Quote\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->observer = new CleanExpiredQuotes($this->storesConfigMock, $this->quoteFactoryMock);
    }

    /**
     * @param array $lifetimes
     * @param array $additionalFilterFields
     * @dataProvider cleanExpiredQuotesDataProvider
     */
    public function testExecute($lifetimes, $additionalFilterFields)
    {
        $this->storesConfigMock->expects($this->once())
            ->method('getStoresConfigByPath')
            ->with($this->equalTo('checkout/cart/delete_quote_after'))
            ->will($this->returnValue($lifetimes));

        $quotesMock = $this->getMockBuilder('Magento\Quote\Model\ResourceModel\Quote\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteFactoryMock->expects($this->exactly(count($lifetimes)))
            ->method('create')
            ->will($this->returnValue($quotesMock));
        $quotesMock->expects($this->exactly((3 + count($additionalFilterFields)) * count($lifetimes)))
            ->method('addFieldToFilter');
        if (!empty($lifetimes)) {
            $quotesMock->expects($this->exactly(count($lifetimes)))
                ->method('walk')
                ->with('delete');
        }
        $this->observer->setExpireQuotesAdditionalFilterFields($additionalFilterFields);
        $this->observer->execute();
    }

    public function cleanExpiredQuotesDataProvider()
    {
        return [
            [[], []],
            [[1 => 100, 2 => 200], []],
            [[1 => 100, 2 => 200], ['field1' => 'condition1', 'field2' => 'condition2']],
        ];
    }
}
