<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class ConflictCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider checkConflictWhenEnableModuleDataProvider
     * @param array $nameReturnMap
     * @param array $conflictReturnMap
     * @param array $enabledModules
     * @param string $moduleName
     * @param array $expected
     */
    public function testCheckConflictsWhenEnableModules(
        $nameReturnMap,
        $conflictReturnMap,
        $enabledModules,
        $moduleName,
        $expected
    ) {
        $packageInfoMock = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $packageInfoMock->expects($this->any())
            ->method('getPackageName')
            ->will($this->returnValueMap($nameReturnMap));
        $packageInfoMock->expects($this->any())
            ->method('getEnabledModules')
            ->will($this->returnValue($enabledModules));
        $packageInfoMock->expects($this->any())
            ->method('getConflict')
            ->will($this->returnValueMap($conflictReturnMap));
        $conflictChecker = new ConflictChecker($packageInfoMock);
        $this->assertEquals($expected, $conflictChecker->checkConflictsWhenEnableModules($moduleName));
    }

    /**
     * @return array
     */
    public function checkConflictWhenEnableModuleDataProvider()
    {
        return [
            [
                [['Vendor_A', 'vendor/module-A'], ['Vendor_B', 'vendor/module-B']],
                [['Vendor_A', ['vendor/module-B']], ['Vendor_B', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_A']]
            ],
            [
                [['Vendor_A', 'vendor/module-A'], ['Vendor_B', 'vendor/module-B']],
                [['Vendor_A', ['vendor/module-B']], ['Vendor_B', []]],
                [],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['Vendor_A', 'vendor/module-A'], ['Vendor_B', 'vendor/module-B']],
                [['Vendor_B', ['vendor/module-A']], ['Vendor_A', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_A']]
            ],
            [
                [['Vendor_A', 'vendor/module-A'], ['Vendor_B', 'vendor/module-B']],
                [['Vendor_B', ['vendor/module-A']], ['Vendor_A', []]],
                [],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['Vendor_A', 'vendor/module-A'], ['Vendor_B', 'vendor/module-B']],
                [['Vendor_A', []], ['Vendor_B', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['Vendor_A', 'vendor/module-A'], ['Vendor_B', 'vendor/module-B'], ['Vendor_C', 'vendor/module-C']],
                [['Vendor_A', []], ['Vendor_B', []], ['Vendor_C', []]],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                ['Vendor_B' => [], 'Vendor_C' => []]
            ],
            [
                [['Vendor_A', 'vendor/module-A'], ['Vendor_B', 'vendor/module-B'], ['Vendor_C', 'vendor/module-C']],
                [['Vendor_A', ['vendor/module-C']], ['Vendor_B', []], ['Vendor_C', []]],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                ['Vendor_B' => [], 'Vendor_C' => ['Vendor_A']]
            ],
            [
                [['Vendor_A', 'vendor/module-A'], ['Vendor_B', 'vendor/module-B'], ['Vendor_C', 'vendor/module-C']],
                [['Vendor_A', []], ['Vendor_B', ['vendor/module-C']], ['Vendor_C', []]],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                ['Vendor_B' => ['Vendor_C'], 'Vendor_C' => ['Vendor_B']]
            ],
        ];
    }
}
