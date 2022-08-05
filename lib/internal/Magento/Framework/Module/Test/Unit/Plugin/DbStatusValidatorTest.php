<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Module\Test\Unit\Plugin;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Module\DbVersionInfo;
use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\Plugin\DbStatusValidator;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class DbStatusValidatorTest extends TestCase
{
    /**
     * @var DbStatusValidator
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_cacheMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var Manager|MockObject
     */
    private $moduleManager;

    /**
     * @var DbVersionInfo|MockObject
     */
    private $dbVersionInfoMock;

    /**
     * @var DeploymentConfig|mixed|MockObject
     */
    private $deploymentConfig;

    protected function setUp(): void
    {
        $this->_cacheMock = $this->getMockBuilder(FrontendInterface::class)
            ->setMethods(['db_is_up_to_date'])
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->subjectMock = $this->createMock(FrontController::class);
        $moduleList = $this->getMockForAbstractClass(ModuleListInterface::class);
        $moduleList->expects($this->any())
            ->method('getNames')
            ->willReturn(['Module_One', 'Module_Two']);

        $this->moduleManager = $this->getMockBuilder(Manager::class)
            ->addMethods(['isDbSchemaUpToDate', 'isDbDataUpToDate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbVersionInfoMock = $this->createMock(DbVersionInfo::class);

        $this->deploymentConfig =$this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_model = new DbStatusValidator(
            $this->_cacheMock,
            $this->dbVersionInfoMock,
            $this->deploymentConfig
        );
    }

    public function testAroundDispatch()
    {
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('db_is_up_to_date')
            ->willReturn(false);
        $returnMap = [
            ['Module_One', 'resource_Module_One', true],
            ['Module_Two', 'resource_Module_Two', true],
        ];
        $this->moduleManager->expects($this->any())
            ->method('isDbSchemaUpToDate')
            ->willReturnMap($returnMap);
        $this->moduleManager->expects($this->any())
            ->method('isDbDataUpToDate')
            ->willReturnMap($returnMap);

        $this->assertNull(
            $this->_model->beforeDispatch($this->subjectMock, $this->requestMock)
        );
    }

    public function testAroundDispatchCached()
    {
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('db_is_up_to_date')
            ->willReturn(true);
        $this->moduleManager->expects($this->never())
            ->method('isDbSchemaUpToDate');
        $this->moduleManager->expects($this->never())
            ->method('isDbDataUpToDate');
        $this->assertNull(
            $this->_model->beforeDispatch($this->subjectMock, $this->requestMock)
        );
    }

    /**
     * @param array $dbVersionErrors
     *
     * @dataProvider aroundDispatchExceptionDataProvider
     */
    public function testAroundDispatchException(array $dbVersionErrors)
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Please upgrade your database:');
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('db_is_up_to_date')
            ->willReturn(false);
        $this->_cacheMock->expects($this->never())->method('save');

        $this->dbVersionInfoMock->expects($this->any())
            ->method('getDbVersionErrors')
            ->willReturn($dbVersionErrors);

        $this->_model->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    /**
     * @return array
     */
    public function aroundDispatchExceptionDataProvider()
    {
        return [
            'schema is outdated' => [
                [
                    [
                        DbVersionInfo::KEY_MODULE => 'Module_One',
                        DbVersionInfo::KEY_TYPE => 'schema',
                        DbVersionInfo::KEY_CURRENT => 'none',
                        DbVersionInfo::KEY_REQUIRED => '1'
                    ]
                ],
            ],
            'data is outdated' => [
                [
                    [
                        DbVersionInfo::KEY_MODULE => 'Module_Two',
                        DbVersionInfo::KEY_TYPE => 'data',
                        DbVersionInfo::KEY_CURRENT => 'none',
                        DbVersionInfo::KEY_REQUIRED => '1'
                    ]
                ],
            ],
            'both schema and data are outdated' => [
                [
                    [
                        DbVersionInfo::KEY_MODULE => 'Module_One',
                        DbVersionInfo::KEY_TYPE => 'schema',
                        DbVersionInfo::KEY_CURRENT => 'none',
                        DbVersionInfo::KEY_REQUIRED => '1'
                    ],
                    [
                        DbVersionInfo::KEY_MODULE => 'Module_Two',
                        DbVersionInfo::KEY_TYPE => 'schema',
                        DbVersionInfo::KEY_CURRENT => 'none',
                        DbVersionInfo::KEY_REQUIRED => '1'
                    ],
                    [
                        DbVersionInfo::KEY_MODULE => 'Module_One',
                        DbVersionInfo::KEY_TYPE => 'data',
                        DbVersionInfo::KEY_CURRENT => 'none',
                        DbVersionInfo::KEY_REQUIRED => '1'
                    ],
                    [
                        DbVersionInfo::KEY_MODULE => 'Module_Two',
                        DbVersionInfo::KEY_TYPE => 'data',
                        DbVersionInfo::KEY_CURRENT => 'none',
                        DbVersionInfo::KEY_REQUIRED => '1'
                    ]
                ],
            ],
        ];
    }

    public function testAroundDispatchBlueGreen()
    {
        $this->deploymentConfig->expects($this->atLeastOnce())
            ->method('get')
            ->with('deployment/blue_green/enabled')
            ->willReturn(1);

        $this->_cacheMock->expects($this->never())
            ->method('load');

        $this->dbVersionInfoMock->expects($this->never())
            ->method('getDbVersionErrors');

        $this->_model->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
