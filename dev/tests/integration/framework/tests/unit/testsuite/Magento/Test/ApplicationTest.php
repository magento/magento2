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
     * Test \Magento\TestFramework\Application will correctly load specified areas.
     *
     * @dataProvider partialLoadAreaDataProvider
     * @param string $areaCode
     * @return void
     */
    public function testPartialLoadArea(string $areaCode)
    {
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

        $area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->getMock();
        $area->expects($this->once())
            ->method('load')
            ->with($this->identicalTo(Area::PART_CONFIG));

        $areaList = $this->getMockBuilder(AreaList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $areaList->expects($this->once())
            ->method('getArea')
            ->with($this->identicalTo($areaCode))
            ->willReturn($area);

        /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject $objectManager */
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->expects($this->once())
            ->method('configure')
            ->with($this->identicalTo([]));
        $objectManager->expects($this->exactly(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                $configScope,
                $configLoader,
                $areaList
            );

        \Magento\TestFramework\Helper\Bootstrap::setObjectManager($objectManager);

        $this->subject->loadArea($areaCode);
    }

    /**
     * Provide test data for testPartialLoadArea().
     *
     * @return array
     */
    public function partialLoadAreaDataProvider()
    {
        return [
            [
                'area_code' => Area::AREA_GLOBAL,
            ],
            [
                'area_code' => Area::AREA_WEBAPI_REST,
            ],
            [
                'area_code' => Area::AREA_WEBAPI_SOAP,
            ],
            [
                'area_code' => Area::AREA_CRONTAB,
            ],
            [
                'area_code' => Area::AREA_GRAPHQL,
            ],
        ];
    }
}
