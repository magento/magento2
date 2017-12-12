<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\State;
use Magento\Framework\Autoload\ClassLoaderWrapper;
use Magento\Framework\Config\Scope;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Shell;
use Magento\TestFramework\Application;

/**
 * Provide tests for \Magento\TestFramework\Application.
 */
class ApplicationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test subject.
     *
     * @var Application
     */
    private $subject;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        /** @var Shell|\PHPUnit_Framework_MockObject_MockObject $shell */
        $shell = $this->createMock(Shell::class);
        /** @var ClassLoaderWrapper|\PHPUnit_Framework_MockObject_MockObject $autoloadWrapper */
        $autoloadWrapper = $this->getMockBuilder(ClassLoaderWrapper::class)
            ->disableOriginalConstructor()->getMock();
        $this->tempDir = '/temp/dir';
        $appMode = \Magento\Framework\App\State::MODE_DEVELOPER;

        $this->subject = new Application(
            $shell,
            $this->tempDir,
            'config.php',
            'global-config.php',
            '',
            $appMode,
            $autoloadWrapper
        );
    }

    /**
     * @covers \Magento\TestFramework\Application::getTempDir
     * @covers \Magento\TestFramework\Application::getDbInstance()
     * @covers \Magento\TestFramework\Application::getInitParams()
     */
    public function testConstructor()
    {
        $this->assertEquals($this->tempDir, $this->subject->getTempDir(), 'Temp directory is not set in Application');

        $initParams = $this->subject->getInitParams();
        $this->assertInternalType('array', $initParams, 'Wrong initialization parameters type');
        $this->assertArrayHasKey(
            Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS,
            $initParams,
            'Directories are not configured'
        );
        $this->assertArrayHasKey(State::PARAM_MODE, $initParams, 'Application mode is not configured');
        $this->assertEquals(
            \Magento\Framework\App\State::MODE_DEVELOPER,
            $initParams[State::PARAM_MODE],
            'Wrong application mode configured'
        );
    }

    /**
     * Test \Magento\TestFramework\Application will correctly load different areas.
     *
     * @dataProvider loadAreaDataProvider
     *
     * @param string $areaCode
     * @param bool $partialLoad
     */
    public function testLoadArea(string $areaCode, bool $partialLoad)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject $objectManagerMock */
        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configScope = $this->getMockBuilder(Scope::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configScope->expects($this->once())
            ->method('setCurrentScope')
            ->with($this->identicalTo($areaCode));
        $configLoader = $this->getMockBuilder(ConfigLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configLoader->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($areaCode))
            ->willReturn([]);
        $areaList = $this->getMockBuilder(AreaList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->getMock();
        $appState = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->once())
            ->method('configure')
            ->with($this->identicalTo([]));
        if ($partialLoad) {
            $objectManagerMock->expects($this->exactly(3))
                ->method('get')
                ->willReturnOnConsecutiveCalls(
                    $configScope,
                    $configLoader,
                    $areaList
                );
            $areaList->expects($this->once())
                ->method('getArea')
                ->with($this->identicalTo($areaCode))
                ->willReturn($area);
            $area->expects($this->once())
                ->method('load')
                ->with($this->identicalTo(Area::PART_CONFIG));
        } else {
            $area->expects($this->once())
                ->method('load');
            $appState->expects($this->once())
                ->method('setAreaCode')
                ->with($this->identicalTo($areaCode));
            $areaList->expects($this->once())
                ->method('getArea')
                ->with($this->identicalTo($areaCode))
                ->willReturn($area);
            $objectManagerMock->expects($this->exactly(5))
                ->method('get')
                ->willReturnOnConsecutiveCalls(
                    $configScope,
                    $configLoader,
                    $areaList,
                    $appState,
                    $areaList
                );
        }
        \Magento\TestFramework\Helper\Bootstrap::setObjectManager($objectManagerMock);
        $this->subject->loadArea($areaCode);

        //restore Object Manager to successfully finish the test.
        \Magento\TestFramework\Helper\Bootstrap::setObjectManager($objectManager);
    }

    /**
     * Provide test data for testLoadArea().
     *
     * @return array
     */
    public function loadAreaDataProvider()
    {
        return [
            [
                'area_code' => Area::AREA_GLOBAL,
                'partial_load' => true,
            ],
            [
                'area_code' => Area::AREA_ADMINHTML,
                'partial_load' => false,
            ],
            [
                'area_code' => Area::AREA_FRONTEND,
                'partial_load' => false,
            ],
            [
                'area_code' => Area::AREA_WEBAPI_REST,
                'partial_load' => true,
            ],
            [
                'area_code' => Area::AREA_WEBAPI_SOAP,
                'partial_load' => true,
            ],
            [
                'area_code' => Area::AREA_CRONTAB,
                'partial_load' => true,
            ],
        ];
    }
}
