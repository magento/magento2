<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Unit\Model\ResourceModel;

use Magento\Authorization\Model\ResourceModel\Rules;
use Magento\Authorization\Model\Rules as RulesModel;
use Magento\Framework\Acl\Builder;
use Magento\Framework\Acl\Data\CacheInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Magento\Authorization\Model\ResourceModel\Rules
 *
 * Covers control flow logic.
 * The resource saving is covered with integration test in \Magento\Authorization\Model\RulesTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RulesTest extends TestCase
{
    /**
     * Test constants
     */
    const TEST_ROLE_ID = 13;

    /**
     * @var Rules
     */
    private $model;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Builder|MockObject
     */
    private $aclBuilderMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var RootResource|MockObject
     */
    private $rootResourceMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $aclDataCacheMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var RulesModel|MockObject
     */
    private $rulesModelMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResources'])
            ->getMock();

        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getResources')
            ->willReturn($this->resourceConnectionMock);

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('default')
            ->willReturn($this->connectionMock);

        $this->resourceConnectionMock->method('getTableName')
            ->with('authorization_rule', 'default')
            ->willReturnArgument(0);

        $this->aclBuilderMock = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigCache'])
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->rootResourceMock = $this->getMockBuilder(RootResource::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->aclDataCacheMock = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->aclBuilderMock->method('getConfigCache')
            ->willReturn($this->aclDataCacheMock);

        $this->rulesModelMock = $this->getMockBuilder(RulesModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRoleId'])
            ->getMock();

        $this->rulesModelMock->method('getRoleId')
            ->willReturn(self::TEST_ROLE_ID);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Rules::class,
            [
                'context' => $this->contextMock,
                'aclBuilder' => $this->aclBuilderMock,
                'logger' => $this->loggerMock,
                'rootResource' => $this->rootResourceMock,
                'aclDataCache' => $this->aclDataCacheMock,
                'default'
            ]
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

        $this->model->saveRel($this->rulesModelMock);
    }

    /**
     * Test LocalizedException throw case.
     */
    public function testLocalizedExceptionOccurrence()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage("TestException");

        $exceptionPhrase = $this->getMockBuilder(Phrase::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $exceptionPhrase->method('render')->willReturn('TestException');

        $exception = new LocalizedException($exceptionPhrase);

        $this->connectionMock->expects($this->once())
            ->method('beginTransaction');

        $this->connectionMock->expects($this->once())
            ->method('delete')
            ->with('authorization_rule', ['role_id = ?' => self::TEST_ROLE_ID])
            ->willThrowException($exception);

        $this->connectionMock->expects($this->once())->method('rollBack');

        $this->model->saveRel($this->rulesModelMock);
    }

    /**
     * Test generic exception throw case.
     */
    public function testGenericExceptionOccurrence()
    {
        $exception = new \Exception('GenericException');

        $this->connectionMock->expects($this->once())
            ->method('beginTransaction');

        $this->connectionMock->expects($this->once())
            ->method('delete')
            ->with('authorization_rule', ['role_id = ?' => self::TEST_ROLE_ID])
            ->willThrowException($exception);

        $this->connectionMock->expects($this->once())->method('rollBack');
        $this->loggerMock->expects($this->once())->method('critical')->with($exception);

        $this->model->saveRel($this->rulesModelMock);
    }
}
