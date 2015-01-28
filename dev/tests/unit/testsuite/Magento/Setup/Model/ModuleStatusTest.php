<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

class ModuleStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Magento\Framework\Module\ModuleList\Loader
     */
    private $moduleLoader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Magento\Framework\App\DeploymentConfig\Reader
     */
    private $configReader;

    public function setUp()
    {
        $this->moduleLoader = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $this->configReader = $this->getMock('Magento\Framework\App\DeploymentConfig\Reader', [], [], '', false);

    }

    /**
     * @param array $expectedAllModules
     * @param array $expectedConfig
     * @param array $expectedResult
     *
     * @dataProvider testGetAllModulesDataProvider
     */
    public function testGetAllModules($expectedAllModules, $expectedConfig, $expectedResult)
    {
        $this->moduleLoader->expects($this->once())->method('load')->will($this->returnValue($expectedAllModules));
        $this->configReader->expects($this->once())->method('load')->will($this->returnValue($expectedConfig));

        $moduleStatus = new ModuleStatus($this->moduleLoader, $this->configReader);
        $allModules = $moduleStatus->getAllModules();
        $this->assertSame($expectedResult[0], $allModules['module1']['selected']);
        $this->assertSame($expectedResult[1], $allModules['module2']['selected']);
        $this->assertSame($expectedResult[2], $allModules['module3']['selected']);
    }

    /**
     * @return array
     */
    public function testGetAllModulesDataProvider()
    {
        $expectedAllModules = ['module1' => '' , 'module2' => '' , 'module3' => '' ];
        $expectedConfig = ['modules' =>['module1' => 0, 'module3' => 0]];
        return [
            [$expectedAllModules, $expectedConfig, [false, true, false]],
            [$expectedAllModules, null, [true, true, true]],
            [$expectedAllModules, [], [true, true, true]],
        ];
    }

    public function testGetAllModulesWithNull()
    {
        $this->moduleLoader->expects($this->once())->method('load')->will($this->returnValue(null));
        $this->configReader->expects($this->never())->method('load');

        $moduleStatus = new ModuleStatus($this->moduleLoader, $this->configReader);
        $allModules = $moduleStatus->getAllModules();
        $this->assertSame([], $allModules);
    }
}
