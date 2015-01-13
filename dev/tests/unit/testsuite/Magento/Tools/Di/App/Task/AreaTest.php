<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\App\Task;

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
     * @var ClassesScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $classesScannerMock;

    /**
     * @var Config\Reader | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configReaderMock;

    /**
     * @var Config\WriterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriterMock;

    protected function setUp()
    {
        $this->areaListMock = $this->getMockBuilder('Magento\Framework\App\AreaList')
            ->disableOriginalConstructor()
            ->getMock();
        $this->classesScannerMock = $this->getMockBuilder('Magento\Tools\Di\Code\Reader\ClassesScanner')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configReaderMock = $this->getMockBuilder('Magento\Tools\Di\Compiler\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configWriterMock = $this->getMockBuilder('Magento\Tools\Di\Compiler\Config\WriterInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDoOperationEmptyPath()
    {
        $areaOperation = new Area(
            $this->areaListMock,
            $this->classesScannerMock,
            $this->configReaderMock,
            $this->configWriterMock
        );

        $this->assertNull($areaOperation->doOperation());
    }

    public function testDoOperationGlobalArea()
    {
        $path = 'path/to/codebase/';
        $generatedConfig = [
            'arguments' => [],
            'nonShared' => [],
            'preferences' => [],
            'instanceTypes' => []
        ];
        $definitions = new DefinitionsCollection();
        $definitions->addDefinition('class', []);
        $areaOperation = new Area(
            $this->areaListMock,
            $this->classesScannerMock,
            $this->configReaderMock,
            $this->configWriterMock,
            [$path]
        );

        $this->areaListMock->expects($this->once())
            ->method('getCodes')
            ->willReturn([]);
        $this->classesScannerMock->expects($this->once())
            ->method('getList')
            ->with($path)
            ->willReturn(['class' => []]);
        $this->configReaderMock->expects($this->once())
            ->method('generateCachePerScope')
            ->with(
                $this->isInstanceOf('Magento\Tools\Di\Definition\Collection'),
                App\Area::AREA_GLOBAL
            )
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
