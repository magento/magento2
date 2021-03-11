<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit\Plugin;

use \Magento\Framework\Module\Plugin\DbStatusValidator;

use Magento\Framework\Module\DbVersionInfo;

/**
 * DbStatus validator test.
 */
class DbStatusValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Module\Plugin\DbStatusValidator
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\Module\DbVersionInfo|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dbVersionInfoMock;

    protected function setUp(): void
    {
        $this->_cacheMock = $this->getMockBuilder(\Magento\Framework\Cache\FrontendInterface::class)
            ->setMethods(['db_is_up_to_date'])
            ->getMockForAbstractClass();
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->subjectMock = $this->createMock(\Magento\Framework\App\FrontController::class);
        $moduleList = $this->getMockForAbstractClass(\Magento\Framework\Module\ModuleListInterface::class);
        $moduleList->expects($this->any())
            ->method('getNames')
            ->willReturn(['Module_One', 'Module_Two']);

        $this->moduleManager = $this->createPartialMock(
            \Magento\Framework\Module\Manager::class,
            ['isDbSchemaUpToDate', 'isDbDataUpToDate']
        );
        $this->dbVersionInfoMock = $this->createMock(\Magento\Framework\Module\DbVersionInfo::class);
        $this->_model = new DbStatusValidator(
            $this->_cacheMock,
            $this->dbVersionInfoMock
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
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
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
}
