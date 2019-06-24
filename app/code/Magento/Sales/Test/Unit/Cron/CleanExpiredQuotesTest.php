<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Cron;

use \Magento\Sales\Cron\CleanExpiredQuotes;
use Magento\Sales\Model\ExpireQuotesFilterFieldsProvider;

/**
 * Tests Magento\Sales\Cron\CleanExpiredQuotes
 */
class CleanExpiredQuotesTest extends \PHPUnit\Framework\TestCase
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
     * @var ExpireQuotesFilterFieldsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $expireQuotesFilterFieldsProviderMock;

    /**
     * @var \Magento\Sales\Cron\CleanExpiredQuotes
     */
    protected $observer;

    protected function setUp()
    {
        $this->storesConfigMock = $this->createMock(\Magento\Store\Model\StoresConfig::class);

        $this->quoteFactoryMock = $this->getMockBuilder(
            \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->expireQuotesFilterFieldsProviderMock = $this->createMock(ExpireQuotesFilterFieldsProvider::class);

        $this->observer = new CleanExpiredQuotes(
            $this->storesConfigMock,
            $this->quoteFactoryMock,
            $this->expireQuotesFilterFieldsProviderMock
        );
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

        $quotesMock = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote\Collection::class)
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
        $this->expireQuotesFilterFieldsProviderMock->expects($this->any())
            ->method('getFields')
            ->willReturn($additionalFilterFields);

        $this->observer->setExpireQuotesAdditionalFilterFields($additionalFilterFields);
        $this->observer->execute();
    }

    /**
     * @return array
     */
    public function cleanExpiredQuotesDataProvider()
    {
        return [
            [[], []],
            [[1 => 100, 2 => 200], []],
            [[1 => 100, 2 => 200], ['field1' => 'condition1', 'field2' => 'condition2']],
        ];
    }
}
