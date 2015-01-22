<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

class StatusFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $moduleListMock = $this->getMock('Magento\Setup\Model\ModuleList', [], [], '', false);
        $objectManagerMock = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $readerMock = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $packageInfoMock = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $conflictCheckerMock = $this->getMock('Magento\Framework\Module\ConflictChecker', [], [], '', false);
        $dependencyCheckerMock = $this->getMock('Magento\Framework\Module\DependencyChecker', [], [], '', false);
        $statusMock = $this->getMock('Magento\Framework\Module\Status', [], [], '', false);
        $returnValueMap = [
            ['Magento\Framework\Module\Dir\Reader', ['moduleList' => $moduleListMock], $readerMock],
            ['Magento\Framework\Module\PackageInfo', ['reader' => $readerMock], $packageInfoMock],
            ['Magento\Framework\Module\ConflictChecker', ['packageInfo' => $packageInfoMock], $conflictCheckerMock],
            ['Magento\Framework\Module\DependencyChecker', ['packageInfo' => $packageInfoMock], $dependencyCheckerMock],
            [
                'Magento\Framework\Module\Status',
                ['conflictChecker' => $conflictCheckerMock, 'dependencyChecker' => $dependencyCheckerMock],
                $statusMock,
            ]
        ];
        $objectManagerMock->expects($this->any())->method('create')->will($this->returnValueMap($returnValueMap));
        $objectManagerFactoryMock = $this->getMock('Magento\Setup\Model\ObjectManagerFactory', [], [], '', false);
        $objectManagerFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($objectManagerMock));

        $statusFactory = new StatusFactory($moduleListMock, $objectManagerFactoryMock);
        $this->assertSame($statusMock, $statusFactory->create());
    }
}
