<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\History;
use Magento\Sales\Helper\Admin;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Order History tab test
 */
class HistoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Admin|MockObject
     */
    protected $adminHelperMock;

    /**
     * @var History
     */
    protected $commentsHistory;

    /**
     * @var Registry|MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDateMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->objectManager->prepareObjectManager();

        $this->coreRegistryMock = $this->createMock(Registry::class);
        $this->adminHelperMock = $this->createMock(Admin::class);

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLocaleDate'])
            ->getMock();

        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->getMock();

        $this->contextMock->expects($this->any())->method('getLocaleDate')->willReturn(
            $this->localeDateMock
        );

        $this->commentsHistory = $this->objectManager->getObject(
            History::class,
            [
                'adminHelper' => $this->adminHelperMock,
                'registry' => $this->coreRegistryMock,
                'context' => $this->contextMock,
                'localeDate' => $this->localeDateMock
            ]
        );
    }

    public function testGetItemComment()
    {
        $expectation = 'Authorized amount of £20.00 Transaction ID: &quot;XXX123123XXX&quot;';
        $item['comment'] = 'Authorized amount of £20.00 Transaction ID: "XXX123123XXX"';
        $this->adminHelperMock->expects($this->once())
            ->method('escapeHtmlWithLinks')
            ->with($item['comment'], ['b', 'br', 'strong', 'i', 'u', 'a'])
            ->willReturn($expectation);
        $this->assertEquals($expectation, $this->commentsHistory->getItemComment($item));
    }

    public function testGetItemCommentIsNotSet()
    {
        $item = [];
        $this->adminHelperMock->expects($this->never())->method('escapeHtmlWithLinks');
        $this->assertEquals('', $this->commentsHistory->getItemComment($item));
    }

    public function testGetItemCreatedAtDate()
    {
        $date = new \DateTime();
        $item = ['created_at' => $date ];

        $this->localeDateMock->expects($this->once())
            ->method('formatDateTime')
            ->with($date, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE)
            ->willReturn('date');

        $this->assertEquals('date', $this->commentsHistory->getItemCreatedAt($item));
    }

    public function testGetItemCreatedAtTime()
    {
        $date = new \DateTime();
        $item = ['created_at' => $date ];

        $this->localeDateMock->expects($this->once())
            ->method('formatDateTime')
            ->with($date, \IntlDateFormatter::NONE, \IntlDateFormatter::MEDIUM)
            ->willReturn('time');

        $this->assertEquals('time', $this->commentsHistory->getItemCreatedAt($item, 'time'));
    }

    public function testGetItemCreatedAtEmpty()
    {
        $item = ['title' => "Test" ];

        $this->localeDateMock->expects($this->never())->method('formatDateTime');
        $this->assertEquals('', $this->commentsHistory->getItemCreatedAt($item));
        $this->assertEquals('', $this->commentsHistory->getItemCreatedAt($item, 'time'));
    }

    public function testGetFullHistoryOrdersSameSecondEntriesByTypeThenId(): void
    {
        $this->mockOrder(
            [
                $this->statusHistory(11, 'Suspected Fraud', '2021-10-01 12:00:00'),
                $this->statusHistory(10, 'Processing', '2021-10-01 12:00:00'),
                $this->statusHistory(9, 'Pending', '2021-10-01 11:59:59'),
            ],
            [],
            [$this->salesDocument(7, '000000007', '2021-10-01 12:00:00')],
            [
                $this->salesDocument(
                    5,
                    '000000005',
                    '2021-10-01 12:00:00',
                    [
                        $this->documentComment(4, '2021-10-01 12:00:00'),
                        $this->documentComment(3, '2021-10-01 12:00:00'),
                    ]
                ),
            ],
            [new DataObject(['id' => 2, 'number' => 'TRACK', 'title' => 'UPS', 'created_at' => '2021-10-01 12:00:00'])]
        );

        $this->assertSame(
            [
                'Pending',
                'Processing',
                'Suspected Fraud',
                'Shipment #000000007 created',
                'Invoice #000000005 created',
                'Invoice #000000005 comment added',
                'Invoice #000000005 comment added',
                'Tracking number TRACK for UPS assigned',
            ],
            array_map(fn (array $item): string => (string)$item['title'], $this->commentsHistory->getFullHistory())
        );
        $this->assertSame(
            [9, 10, 11, 7, 5, 3, 4, 2],
            array_column($this->commentsHistory->getFullHistory(), 'entity_id')
        );
    }

    public function testGetFullHistoryOrdersByTimestampBeforeType(): void
    {
        $this->mockOrder(
            [$this->statusHistory(10, 'Complete', '2021-10-01 12:00:05')],
            [$this->salesDocument(8, '000000008', '2021-10-01 12:00:04')],
            [],
            [$this->salesDocument(5, '000000005', '2021-10-01 12:00:01')],
            []
        );

        $this->assertSame(
            ['Invoice #000000005 created', 'Credit memo #000000008 created', 'Complete'],
            array_map(fn (array $item): string => (string)$item['title'], $this->commentsHistory->getFullHistory())
        );
    }

    /**
     * @param mixed $notified
     */
    #[DataProvider('notificationValueDataProvider')]
    public function testGetFullHistoryPassesStoredValuesThrough($notified): void
    {
        $this->mockOrder(
            [$this->statusHistory(10, 'Processing', '2021-10-01 12:00:00', $notified, null)],
            [
                $this->salesDocument(
                    8,
                    '000000008',
                    '2021-10-01 12:00:01',
                    [$this->documentComment(1, '2021-10-01 12:00:01', $notified, null)],
                    $notified
                ),
            ],
            [
                $this->salesDocument(
                    7,
                    '000000007',
                    '2021-10-01 12:00:02',
                    [$this->documentComment(1, '2021-10-01 12:00:02', $notified, null)],
                    $notified
                ),
            ],
            [
                $this->salesDocument(
                    5,
                    '000000005',
                    '2021-10-01 12:00:03',
                    [$this->documentComment(1, '2021-10-01 12:00:03', $notified, null)],
                    $notified
                ),
            ],
            []
        );
        $this->adminHelperMock->method('escapeHtmlWithLinks')->willReturnArgument(0);

        $history = $this->commentsHistory->getFullHistory();

        $this->assertCount(7, $history);
        foreach ($history as $item) {
            $this->assertSame($notified, $item['notified']);
            $this->assertSame('', $this->commentsHistory->getItemComment($item));
        }
        $this->assertSame(
            $notified === Order\Status\History::CUSTOMER_NOTIFICATION_NOT_APPLICABLE,
            $this->commentsHistory->isCustomerNotificationNotApplicable($history[0])
        );
    }

    /**
     * @return array
     */
    public static function notificationValueDataProvider(): array
    {
        return [
            'null' => [null],
            'not notified' => ['0'],
            'notified' => ['1'],
            'not applicable' => [Order\Status\History::CUSTOMER_NOTIFICATION_NOT_APPLICABLE],
        ];
    }

    /**
     * @param DataObject[] $statusHistory
     * @param DataObject[] $creditMemos
     * @param DataObject[] $shipments
     * @param DataObject[] $invoices
     * @param DataObject[] $tracks
     * @return void
     */
    private function mockOrder(
        array $statusHistory,
        array $creditMemos,
        array $shipments,
        array $invoices,
        array $tracks
    ): void {
        $order = $this->createMock(Order::class);
        $order->method('getAllStatusHistory')->willReturn($statusHistory);
        $order->method('getCreditmemosCollection')->willReturn($creditMemos);
        $order->method('getShipmentsCollection')->willReturn($shipments);
        $order->method('getInvoiceCollection')->willReturn($invoices);
        $order->method('getTracksCollection')->willReturn($tracks);
        $this->coreRegistryMock->method('registry')->with('current_order')->willReturn($order);
        $this->localeDateMock->method('date')->willReturnArgument(0);
    }

    /**
     * @param int $id
     * @param string $label
     * @param string $createdAt
     * @param mixed $notified
     * @param string|null $comment
     * @return DataObject
     */
    private function statusHistory(
        int $id,
        string $label,
        string $createdAt,
        $notified = '0',
        ?string $comment = 'comment'
    ): DataObject {
        return new DataObject(
            [
                'id' => $id,
                'status_label' => $label,
                'is_customer_notified' => $notified,
                'created_at' => $createdAt,
                'comment' => $comment,
            ]
        );
    }

    /**
     * @param int $id
     * @param string $incrementId
     * @param string $createdAt
     * @param DataObject[] $comments
     * @param mixed $emailSent
     * @return DataObject
     */
    private function salesDocument(
        int $id,
        string $incrementId,
        string $createdAt,
        array $comments = [],
        $emailSent = '0'
    ): DataObject {
        return new DataObject(
            [
                'id' => $id,
                'increment_id' => $incrementId,
                'created_at' => $createdAt,
                'email_sent' => $emailSent,
                'comments_collection' => $comments,
            ]
        );
    }

    /**
     * @param int $id
     * @param string $createdAt
     * @param mixed $notified
     * @param string|null $comment
     * @return DataObject
     */
    private function documentComment(
        int $id,
        string $createdAt,
        $notified = '0',
        ?string $comment = 'comment'
    ): DataObject {
        return new DataObject(
            ['id' => $id, 'is_customer_notified' => $notified, 'created_at' => $createdAt, 'comment' => $comment]
        );
    }
}
