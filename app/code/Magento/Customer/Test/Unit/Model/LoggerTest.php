<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Customer log data logger test.
 */
class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Customer log data logger.
     *
     * @var \Magento\Customer\Model\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\LogFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logFactory;

    /**
     * Resource instance.
     *
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * DB connection instance.
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->connection = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['select', 'insertOnDuplicate', 'fetchRow'],
            [],
            '',
            false
        );
        $this->resource = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->logFactory = $this->getMock('\Magento\Customer\Model\LogFactory', ['create'], [], '', false);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->logger = $objectManagerHelper->getObject(
            '\Magento\Customer\Model\Logger',
            [
                'resource' => $this->resource,
                'logFactory' => $this->logFactory
            ]
        );
    }

    /**
     * @param int $customerId
     * @param array $data
     * @dataProvider testLogDataProvider
     * @return void
     */
    public function testLog($customerId, $data)
    {
        $tableName = 'customer_log_table_name';
        $data = array_filter($data);

        if (!$data) {
            $this->setExpectedException('\InvalidArgumentException', 'Log data is empty');
            $this->logger->log($customerId, $data);
            return;
        }

        $this->resource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->resource->expects($this->once())
            ->method('getTableName')
            ->with('customer_log')
            ->willReturn($tableName);
        $this->connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with($tableName, array_merge(['customer_id' => $customerId], $data), array_keys($data));

        $this->assertEquals($this->logger, $this->logger->log($customerId, $data));
    }

    /**
     * @return array
     */
    public function testLogDataProvider()
    {
        return [
            [235, ['last_login_at' => '2015-03-04 12:00:00']],
            [235, ['last_login_at' => null]],
        ];
    }

    /**
     * @param int $customerId
     * @param array $data
     * @dataProvider testGetDataProvider
     * @return void
     */
    public function testGet($customerId, $data)
    {
        $logArguments = [
            'customerId' => $data['customer_id'],
            'lastLoginAt' => $data['last_login_at'],
            'lastLogoutAt' => $data['last_logout_at'],
            'lastVisitAt' => $data['last_visit_at']
        ];

        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);

        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();
        $select->expects($this->any())->method('order')->willReturnSelf();
        $select->expects($this->any())->method('limit')->willReturnSelf();

        $this->connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $this->resource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->connection->expects($this->any())
            ->method('fetchRow')
            ->with($select)
            ->willReturn($data);

        $log = $this->getMock(
            'Magento\Customer\Model\Log',
            [],
            $logArguments
        );

        $this->logFactory->expects($this->any())
            ->method('create')
            ->with($logArguments)
            ->willReturn($log);

        $this->assertEquals($log, $this->logger->get($customerId));
    }

    /**
     * @return array
     */
    public function testGetDataProvider()
    {
        return [
            [
                235,
                [
                    'customer_id' => 369,
                    'last_login_at' => '2015-03-04 12:00:00',
                    'last_visit_at' => '2015-03-04 12:01:00',
                    'last_logout_at' => '2015-03-04 12:05:00',
                ]
            ],
            [
                235,
                [
                    'customer_id' => 369,
                    'last_login_at' => '2015-03-04 12:00:00',
                    'last_visit_at' => '2015-03-04 12:01:00',
                    'last_logout_at' => null,
                ]
            ],
            [
                235,
                [
                    'customer_id' => null,
                    'last_login_at' => null,
                    'last_visit_at' => null,
                    'last_logout_at' => null,
                ]
            ],
        ];
    }
}
