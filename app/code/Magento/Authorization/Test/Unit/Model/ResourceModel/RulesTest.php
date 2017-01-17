<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Test\Unit\Model\ResourceModel;

/**
 * Unit test for Rules resource model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RulesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test constants
     */
    const TEST_ROLE_ID = 13;

    /**
     * @var \Magento\Authorization\Model\ResourceModel\Rules
     */
    private $model;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\Acl\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aclBuilderMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Framework\Acl\RootResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rootResourceMock;

    /**
     * @var \Magento\Framework\Acl\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aclCacheMock;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aclDataCacheMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Authorization\Model\Rules|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResources'])
            ->getMock();

        $this->resourceConnectionMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue($this->resourceConnectionMock));

        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('connection')
            ->will($this->returnValue($this->connectionMock));

        $this->resourceConnectionMock->expects($this->any())
            ->method('getTableName')
            ->with('authorization_rule', 'connection')
            ->will($this->returnArgument(0));

        $this->aclBuilderMock = $this->getMockBuilder(\Magento\Framework\Acl\Builder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigCache'])
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->rootResourceMock = $this->getMockBuilder(\Magento\Framework\Acl\RootResource::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->aclCacheMock = $this->getMockBuilder(\Magento\Framework\Acl\CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->aclDataCacheMock = $this->getMockBuilder(\Magento\Framework\Acl\Data\CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->aclBuilderMock->expects($this->any())
            ->method('getConfigCache')
            ->will($this->returnValue($this->aclDataCacheMock));

        $this->ruleMock = $this->getMockBuilder(\Magento\Authorization\Model\Rules::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRoleId'])
            ->getMock();

        $this->ruleMock->expects($this->any())
            ->method('getRoleId')
            ->will($this->returnValue(self::TEST_ROLE_ID));

        $this->model = new \Magento\Authorization\Model\ResourceModel\Rules(
            $this->contextMock,
            $this->aclBuilderMock,
            $this->loggerMock,
            $this->rootResourceMock,
            $this->aclCacheMock,
            'connection',
            $this->aclDataCacheMock
        );
    }

    /**
     * Test save with no resources posted.
     */
    public function testSaveRelNoResources()
    {
        $this->connectionMock->expects($this->once())
            ->method('beginTransaction');
        $this->connectionMock->expects($this->once())
            ->method('delete')
            ->with('authorization_rule', ['role_id = ?' => self::TEST_ROLE_ID]);
        $this->connectionMock->expects($this->once())
            ->method('commit');

        $this->aclDataCacheMock->expects($this->once())
            ->method('clean');

        $this->model->saveRel($this->ruleMock);
    }

    /**
     * Test LocalizedException throw case.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage TestException
     */
    public function testLocalizedExceptionOccurance()
    {
        $exceptionPhrase = $this->getMockBuilder(\Magento\Framework\Phrase::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $exceptionPhrase->expects($this->any())->method('render')->will($this->returnValue('TestException'));

        $exception = new \Magento\Framework\Exception\LocalizedException($exceptionPhrase);

        $this->connectionMock->expects($this->once())
            ->method('beginTransaction');

        $this->connectionMock->expects($this->once())
            ->method('delete')
            ->with('authorization_rule', ['role_id = ?' => self::TEST_ROLE_ID])
            ->will($this->throwException($exception));

        $this->connectionMock->expects($this->once())->method('rollBack');

        $this->model->saveRel($this->ruleMock);
    }

    /**
     * Test generic exception throw case.
     */
    public function testGenericExceptionOccurance()
    {
        $exception = new \Exception('GenericException');

        $this->connectionMock->expects($this->once())
            ->method('beginTransaction');

        $this->connectionMock->expects($this->once())
            ->method('delete')
            ->with('authorization_rule', ['role_id = ?' => self::TEST_ROLE_ID])
            ->will($this->throwException($exception));

        $this->connectionMock->expects($this->once())->method('rollBack');
        $this->loggerMock->expects($this->once())->method('critical')->with($exception);

        $this->model->saveRel($this->ruleMock);
    }
}
