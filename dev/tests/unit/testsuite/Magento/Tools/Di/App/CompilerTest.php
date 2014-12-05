<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Di\App;

class CompilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Di\App\Compiler
     */
    private $model;

    /**
     * @var \Magento\Framework\App\AreaList | \PHPUnit_Framework_MockObject_MockObject
     */
    private $areaList;

    /**
     * @var \Magento\Tools\Di\Code\Reader\ClassesScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $classesScanner;

    /**
     * @var \Magento\Tools\Di\Code\Generator\InterceptionConfigurationBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    private $interceptionConfigurationBuilder;

    /**
     * @var \Magento\Tools\Di\Compiler\Config\Reader | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configReader;

    /**
     * @var \Magento\Tools\Di\Compiler\Config\Writer\Filesystem | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriter;

    protected function setUp()
    {
        $this->areaList = $this->getMockBuilder('\Magento\Framework\App\AreaList')
            ->disableOriginalConstructor()
            ->getMock();

        $this->classesScanner = $this->getMockBuilder('\Magento\Tools\Di\Code\Reader\ClassesScanner')
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();

        $this->interceptionConfigurationBuilder = $this->getMockBuilder(
            '\Magento\Tools\Di\Code\Generator\InterceptionConfigurationBuilder'
        )->disableOriginalConstructor()->getMock();

        $this->configReader = $this->getMockBuilder('\Magento\Tools\Di\Compiler\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configWriter = $this->getMockBuilder('\Magento\Tools\Di\Compiler\Config\Writer\Filesystem')
            ->setMethods(['write'])
            ->getMock();

        $this->model = new \Magento\Tools\Di\App\Compiler(
            $this->areaList,
            $this->classesScanner,
            $this->interceptionConfigurationBuilder,
            $this->configReader,
            $this->configWriter
        );
    }

    public function testLaunch()
    {
        $this->classesScanner->expects($this->exactly(3))
            ->method('getList')
            ->willReturn([]);

        $this->configReader->expects($this->any())
            ->method('generateCachePerScope')
            ->willReturn([]);

        $areaListResult = ['global', 'frontend', 'admin'];
        $this->areaList->expects($this->once())
            ->method('getCodes')
            ->willReturn($areaListResult);

        $count = count($areaListResult) + 1;
        $this->configWriter->expects($this->exactly($count))
            ->method('write');

        $this->interceptionConfigurationBuilder->expects($this->exactly($count))
            ->method('addAreaCode');

        $this->interceptionConfigurationBuilder->expects($this->once())
            ->method('getInterceptionConfiguration')
            ->willReturn([]);

        $this->assertInstanceOf('\Magento\Framework\App\Console\Response', $this->model->launch());
    }
}
