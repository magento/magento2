<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Test\Unit\Model\ResourceModel;

use Magento\AdminNotification\Model\ResourceModel\Inbox;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


class InboxTest extends TestCase
{
    /**
     * @var Inbox
     */
    private $inbox;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var MockObject|AdapterInterface
     */
    private $mockedConnection;

    protected function setUp()
    {
        parent::setUp();
        $mockedSelect = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockedSelect->method('from')->willReturnSelf();
        $mockedSelect->method('where')->willReturnSelf();
        $this->mockedConnection = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $this->mockedConnection
            ->method('select')
            ->willReturn($mockedSelect);
        /** @var ResourceConnection|MockObject $mockedResourceConnection */
        $mockedResourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockedResourceConnection
            ->method('getConnection')
            ->willReturn($this->mockedConnection);
        /** @var Context|MockObject $mockedContext */
        $mockedContext = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockedContext
            ->method('getResources')
            ->willReturn($mockedResourceConnection);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->inbox = $this->objectManagerHelper->getObject(
            Inbox::class,
            [
                'context' => $mockedContext,
            ]
        );
    }

    public function testParse()
    {
        // Setup:
        $mockedInbox = $this->getMockBuilder(\Magento\AdminNotification\Model\Inbox::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fakeData = [
            [
                'severity' => 1,
                'date_added' => '2021-01-01 12:00:00',
                'title' => 'Foo',
                'description' => 'Bar',
                'url' => 'http://www.example.com',
                'internal' => true,
            ],
        ];

        // Set expectations:
        $this->mockedConnection
            ->expects($this->never())
            ->method('fetchRow');
        $this->mockedConnection
            ->expects($this->once())
            ->method('insert')
            ->with(
                null,
                [
                    'severity' => 1,
                    'date_added' => '2021-01-01 12:00:00',
                    'title' => 'Foo',
                    'description' => 'Bar',
                    'url' => 'http://www.example.com',
                ]
            );

        // Exercise:
        $this->inbox->parse($mockedInbox, $fakeData);
    }

    public function testNotificationWithoutUrlIsNulled()
    {
        // Setup:
        $mockedInbox = $this->getMockBuilder(\Magento\AdminNotification\Model\Inbox::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fakeData = [
            [
                'severity' => 1,
                'date_added' => '2021-01-01 12:00:00',
                'title' => 'Foo',
                'description' => 'Bar',
                'url' => '',
                'internal' => true,
            ],
        ];

        // Set expectations:
        $this->mockedConnection
            ->expects($this->never())
            ->method('fetchRow');
        $this->mockedConnection
            ->expects($this->once())
            ->method('insert')
            ->with(
                null,
                [
                    'severity' => 1,
                    'date_added' => '2021-01-01 12:00:00',
                    'title' => 'Foo',
                    'description' => 'Bar',
                ]
            );

        // Exercise:
        $this->inbox->parse($mockedInbox, $fakeData);
    }

    public function testInternalNotificationsWithIdenticalDataAreStored()
    {
        // Setup:
        $mockedInbox = $this->getMockBuilder(\Magento\AdminNotification\Model\Inbox::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fakeData = [
            [
                'severity' => 1,
                'date_added' => '2021-01-01 12:00:00',
                'title' => 'Foo',
                'description' => 'Bar',
                'internal' => true,
            ],
        ];

        // Set expectations:
        $this->mockedConnection
            ->expects($this->never())
            ->method('fetchRow');
        $this->mockedConnection
            ->expects($this->once())
            ->method('insert')
            ->with(
                null,
                [
                    'severity' => 1,
                    'date_added' => '2021-01-01 12:00:00',
                    'title' => 'Foo',
                    'description' => 'Bar',
                ]
            );

        // Exercise:
        $this->inbox->parse($mockedInbox, $fakeData);
    }

    public function testNonInternalNotificationsWithIdenticalDataAreNotStored()
    {
        // Setup:
        $mockedInbox = $this->getMockBuilder(\Magento\AdminNotification\Model\Inbox::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fakeData = [
            [
                'severity' => 1,
                'date_added' => '2021-01-01 12:00:00',
                'title' => 'Foo',
                'description' => 'Bar',
                'internal' => false,
            ],
        ];
        $this->mockedConnection
            ->method('fetchRow')
            ->willReturn($fakeData);

        // Set expectations:
        $this->mockedConnection
            ->expects($this->never())
            ->method('insert');

        // Exercise:
        $this->inbox->parse($mockedInbox, $fakeData);
    }
}
