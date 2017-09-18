<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\Module\Plugin;

use Magento\Framework\Module\Plugin\DbStatusValidator as DbStatusValidatorPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Cache\FrontendInterface as FrontendCacheInterface;
use Magento\Framework\Module\DbVersionInfo;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

class DbStatusValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DbStatusValidatorPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var FrontendCacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var DbVersionInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbVersionInfoMock;

    /**
     * @var FrontController|\PHPUnit_Framework_MockObject_MockObject
     */
    private $frontControllerMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    protected function setUp()
    {
        $this->cacheMock = $this->getMockBuilder(FrontendCacheInterface::class)
            ->getMockForAbstractClass();
        $this->dbVersionInfoMock = $this->getMockBuilder(DbVersionInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontControllerMock = $this->getMockBuilder(FrontController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            DbStatusValidatorPlugin::class,
            [
                'cache' => $this->cacheMock,
                'dbVersionInfo' => $this->dbVersionInfoMock
            ]
        );
    }

    public function testBeforeDispatchUpToDate()
    {
        $this->cacheMock->expects(static::any())
            ->method('load')
            ->with('db_is_up_to_date')
            ->willReturn('cache_data');
        $this->dbVersionInfoMock->expects(static::never())
            ->method('getDbVersionErrors');
        $this->cacheMock->expects(static::never())
            ->method('save');

        $this->plugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }

    public function testBeforeDispatchOutOfDateNoErrors()
    {
        $this->cacheMock->expects(static::any())
            ->method('load')
            ->with('db_is_up_to_date')
            ->willReturn(false);
        $this->dbVersionInfoMock->expects(static::once())
            ->method('getDbVersionErrors')
            ->willReturn([]);
        $this->cacheMock->expects(static::once())
            ->method('save')
            ->with('true', 'db_is_up_to_date', [], null)
            ->willReturn(true);

        $this->plugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }

    public function testBeforeDispatchOutOfDateWithErrors()
    {
        $errors = [
            [
                DbVersionInfo::KEY_MODULE => 'Magento_Module1',
                DbVersionInfo::KEY_TYPE => 'schema',
                DbVersionInfo::KEY_CURRENT => '3.3.3',
                DbVersionInfo::KEY_REQUIRED => '4.4.4'
            ],
            [
                DbVersionInfo::KEY_MODULE => 'Magento_Module2',
                DbVersionInfo::KEY_TYPE => 'data',
                DbVersionInfo::KEY_CURRENT => '2.8.7',
                DbVersionInfo::KEY_REQUIRED => '5.1.6'
            ]
        ];
        $message = 'Please upgrade your database: '
            . "Run \"bin/magento setup:upgrade\" from the Magento root directory.\n"
            . "The following modules are outdated:\n"
            . "Magento_Module1 schema: current version - 3.3.3, required version - 4.4.4\n"
            . "Magento_Module2 data: current version - 2.8.7, required version - 5.1.6";

        $this->cacheMock->expects(static::any())
            ->method('load')
            ->with('db_is_up_to_date')
            ->willReturn(false);
        $this->dbVersionInfoMock->expects(static::once())
            ->method('getDbVersionErrors')
            ->willReturn($errors);
        $this->cacheMock->expects(static::never())
            ->method('save');

        $this->expectException(LocalizedException::class, $message);
        $this->plugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }
}
