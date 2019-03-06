<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Plugin;

use Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\Quote\Plugin\UpdateQuoteItemStore;
use Magento\Quote\Model\QuoteRepository;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\Store;
use Magento\Quote\Model\Quote;

/**
 * Unit test for Magento\Quote\Plugin\UpdateQuoteItemStore.
 */
class UpdateQuoteItemStoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UpdateQuoteItemStore
     */
    private $model;

    /**
     * @var StoreSwitcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var QuoteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->checkoutSessionMock = $this->createPartialMock(
            Session::class,
            ['getQuote']
        );
        $this->quoteMock = $this->createPartialMock(
            Quote::class,
            ['getIsActive', 'setStoreId', 'getItemsCollection']
        );
        $this->storeMock = $this->createPartialMock(
            Store::class,
            ['getId']
        );
        $this->quoteRepositoryMock = $this->createPartialMock(
            QuoteRepository::class,
            ['save']
        );
        $this->subjectMock = $this->createMock(StoreSwitcherInterface::class);

        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);

        $this->model = $objectManager->getObject(
            UpdateQuoteItemStore::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'checkoutSession' => $this->checkoutSessionMock,
            ]
        );
    }

    /**
     * Unit test for afterSwitch method with active quote.
     *
     * @return void
     */
    public function testWithActiveQuote()
    {
        $storeId = 1;
        $this->quoteMock->expects($this->once())->method('getIsActive')->willReturn(true);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($storeId);
        $this->quoteMock->expects($this->once())->method('setStoreId')->with($storeId)->willReturnSelf();
        $quoteItem = $this->createMock(Item::class);
        $this->quoteMock->expects($this->once())->method('getItemsCollection')->willReturnSelf($quoteItem);

        $this->model->afterSwitch(
            $this->subjectMock,
            'magento2.loc',
            $this->storeMock,
            $this->storeMock,
            'magento2.loc'
        );
    }

    /**
     * Unit test for afterSwitch method without active quote.
     *
     * @dataProvider getIsActive
     * @param bool|null $isActive
     * @return void
     */
    public function testWithoutActiveQuote($isActive)
    {
        $this->quoteMock->expects($this->once())->method('getIsActive')->willReturn($isActive);
        $this->quoteRepositoryMock->expects($this->never())->method('save');

        $this->model->afterSwitch(
            $this->subjectMock,
            'magento2.loc',
            $this->storeMock,
            $this->storeMock,
            'magento2.loc'
        );
    }

    /**
     * Data provider for method testWithoutActiveQuote.
     * @return array
     */
    public function getIsActive()
    {
        return [
            [false],
            [null],
        ];
    }
}
