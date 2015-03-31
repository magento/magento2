<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Test\Unit\App\Task;

use Magento\Framework\App;
use Magento\Tools\Di\App\Task\Operation\Area;
use Magento\Tools\Di\Code\Reader\ClassesScanner;
use Magento\Tools\Di\Compiler\Config;
use Magento\Tools\Di\Definition\Collection as DefinitionsCollection;

class AreaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var App\AreaList | \PHPUnit_Framework_MockObject_MockObject
     */
    private $areaListMock;

    /**
     * @var \Magento\Tools\Di\Code\Reader\Decorator\Area | \PHPUnit_Framework_MockObject_MockObject
     */
    private $areaInstancesNamesList;

    /**
     * @var Config\Reader | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configReaderMock;

    /**
     * @var Config\WriterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriterMock;

    /**
     * @var \Magento\Tools\Di\Compiler\Config\ModificationChain | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configChain;

    protected function setUp()
    {
        $this->areaListMock = $this->getMockBuilder('Magento\Framework\App\AreaList')
            ->disableOriginalConstructor()
            ->getMock();
        $this->areaInstancesNamesList = $this->getMockBuilder('\Magento\Tools\Di\Code\Reader\Decorator\Area')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configReaderMock = $this->getMockBuilder('Magento\Tools\Di\Compiler\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configWriterMock = $this->getMockBuilder('Magento\Tools\Di\Compiler\Config\WriterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configChain = $this->getMockBuilder('Magento\Tools\Di\Compiler\Config\ModificationChain')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDoOperationEmptyPath()
    {
        $areaOperation = new Area(
            $this->areaListMock,
            $this->areaInstancesNamesList,
            $this->configReaderMock,
            $this->configWriterMock,
            $this->configChain
        );

        $this->assertNull($areaOperation->doOperation());
    }

    public function testDoOperationGlobalArea()
    {
        $path = 'path/to/codebase/';
        $arguments = ['class' => []];
        $generatedConfig = [
            'arguments' => $arguments,
            'preferences' => [],
            'instanceTypes' => []
        ];

        $areaOperation = new Area(
            $this->areaListMock,
            $this->areaInstancesNamesList,
            $this->configReaderMock,
            $this->configWriterMock,
            $this->configChain,
            [$path]
        );

        $this->areaListMock->expects($this->once())
            ->method('getCodes')
            ->willReturn([]);
        $this->areaInstancesNamesList->expects($this->once())
            ->method('getList')
            ->with($path)
            ->willReturn($arguments);
        $this->configReaderMock->expects($this->once())
            ->method('generateCachePerScope')
            ->with(
                $this->isInstanceOf('Magento\Tools\Di\Definition\Collection'),
                App\Area::AREA_GLOBAL
            )
            ->willReturn($generatedConfig);
        $this->configChain->expects($this->once())
            ->method('modify')
            ->with($generatedConfig)
            ->willReturn($generatedConfig);

        $this->configWriterMock->expects($this->once())
            ->method('write')
            ->with(
                App\Area::AREA_GLOBAL,
                $generatedConfig
            );

        $areaOperation->doOperation();
    }
}
