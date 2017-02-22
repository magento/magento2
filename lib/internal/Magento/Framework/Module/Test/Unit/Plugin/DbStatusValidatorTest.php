<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit\Plugin;

use \Magento\Framework\Module\Plugin\DbStatusValidator;

use Magento\Framework\Module\DbVersionInfo;

class DbStatusValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Plugin\DbStatusValidator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dbUpdaterMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\Module\DbVersionInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbVersionInfoMock;

    protected function setUp()
    {
        $this->_cacheMock = $this->getMock('\Magento\Framework\Cache\FrontendInterface');
        $this->_dbUpdaterMock = $this->getMock('\Magento\Framework\Module\Updater', [], [], '', false);
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->subjectMock = $this->getMock('Magento\Framework\App\FrontController', [], [], '', false);
        $moduleList = $this->getMockForAbstractClass('\Magento\Framework\Module\ModuleListInterface');
        $moduleList->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Module_One', 'Module_Two']));

        $this->moduleManager = $this->getMock('\Magento\Framework\Module\Manager', [], [], '', false);
        $this->dbVersionInfoMock = $this->getMock('\Magento\Framework\Module\DbVersionInfo', [], [], '', false);
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
            ->will($this->returnValue(false));
        $returnMap = [
            ['Module_One', 'resource_Module_One', true],
            ['Module_Two', 'resource_Module_Two', true],
        ];
        $this->moduleManager->expects($this->any())
            ->method('isDbSchemaUpToDate')
            ->will($this->returnValueMap($returnMap));
        $this->moduleManager->expects($this->any())
            ->method('isDbDataUpToDate')
            ->will($this->returnValueMap($returnMap));

        $this->assertEquals(
            'Expected',
            $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }

    public function testAroundDispatchCached()
    {
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('db_is_up_to_date')
            ->will($this->returnValue(true));
        $this->moduleManager->expects($this->never())
            ->method('isDbSchemaUpToDate');
        $this->moduleManager->expects($this->never())
            ->method('isDbDataUpToDate');
        $this->assertEquals(
            'Expected',
            $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }

    /**
     * @param array $dbVersionErrors
     *
     * @dataProvider aroundDispatchExceptionDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please upgrade your database:
     */
    public function testAroundDispatchException(array $dbVersionErrors)
    {
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('db_is_up_to_date')
            ->will($this->returnValue(false));
        $this->_cacheMock->expects($this->never())->method('save');

        $this->dbVersionInfoMock->expects($this->any())
            ->method('getDbVersionErrors')
            ->will($this->returnValue($dbVersionErrors));

        $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock);
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
