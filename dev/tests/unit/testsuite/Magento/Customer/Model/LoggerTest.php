<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

/**
 * Customer log data logger test.
 *
 * @package Magento\Customer\Model
 */
class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Customer log model.
     *
     * @var \Magento\Customer\Model\Log|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $log;

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
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * DB connection instance.
     *
     * @var \Magento\Framework\DB\Adapter\Pdo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $logData = [
        'customer_id' => 369,
        'last_login_at' => '2015-03-04 12:00:00',
        'last_visit_at' => '2015-03-04 12:01:00',
        'last_logout_at' => '2015-03-04 12:05:00',
    ];

    protected function setUp()
    {
        $select = $this->getMock(
            'Magento\Framework\DB\Select', [], [], '', false
        );
        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();
        $select->expects($this->any())->method('order')->willReturnSelf();
        $select->expects($this->any())->method('limit')->willReturnSelf();

        $this->adapter = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo', ['select', 'insertOnDuplicate', 'fetchRow'], [], '', false
        );
        $this->adapter->expects($this->any())->method('select')->willReturn($select);

        $this->resource = $this->getMock(
            'Magento\Framework\App\Resource', ['getConnection', 'getTableName'], [], '', false
        );
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->adapter);
        $this->resource->expects($this->any())->method('getConnection')->willReturnArgument(0);

        $this->log = $this->getMock(
            'Magento\Customer\Model\Log',
            [],
            [
                'customerId' => $this->logData['customer_id'],
                'lastLoginAt' => $this->logData['last_login_at'],
                'lastLogoutAt' => $this->logData['last_logout_at'],
                'lastVisitAt' => $this->logData['last_visit_at']
            ]
        );

        $this->logFactory = $this->getMock(
            '\Magento\Customer\Model\LogFactory', ['create'], [], '', false
        );
        $this->logFactory->expects($this->any())->method('create')->willReturn($this->log);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

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
     */
    public function testLog($customerId, $data)
    {
        $data = array_filter($data);

        if (!$data) {
            try {
                $this->logger->log($customerId, $data);
            }
            catch (\InvalidArgumentException $expected) {
                return;
            }
            $this->fail('An expected exception has not been raised');
        }

        $this->resource->expects($this->once())->method('getConnection');
        $this->adapter->expects($this->once())->method('insertOnDuplicate');

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
     */
    public function testGet($customerId, $data)
    {
        $this->adapter->expects($this->any())->method('fetchRow')->willReturn($data);

        if (!$data) {
            try {
                $this->logger->get($customerId);
            }
            catch (\LogicException $expected) {
                return;
            }
            $this->fail('An expected exception has not been raised');
        }

        $this->assertEquals($this->log, $this->logger->get($customerId));
    }

    /**
     * @return array
     */
    public function testGetDataProvider()
    {
        return [
            [235, $this->logData],
            [235, null],
        ];
    }
}
