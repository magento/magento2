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
     * @param array $mapperReturnMap
     * @param array $data
     * @param array $enabledData
     * @param string $moduleName
     */
    public function testCheckConflictsWhenEnableModules(
        $mapperReturnMap,
        $data,
        $enabledData,
        $moduleName,
        $conflictingModules
    ) {
        $mapperMock = $this->getMock('Magento\Framework\Module\Mapper', [], [], '', false);
        $mapperMock->expects($this->any())
            ->method('packageNameToModuleFullName')
            ->will($this->returnValueMap($mapperReturnMap));
        $conflictChecker = new ConflictChecker($mapperMock);
        $conflictChecker->setModulesData($data);
        $conflictChecker->setEnabledModules($enabledData);
        $this->assertEquals($conflictingModules, $conflictChecker->checkConflictsWhenEnableModules($moduleName));
    }

    /**
     * @return array
     */
    public function checkConflictWhenEnableModuleDataProvider()
    {
        return [
            [
                [['vendor/module-A', 'Vendor_A'], ['vendor/module-B', 'Vendor_B']],
                ['Vendor_A' => '{"conflict":{"vendor/module-B":0.1}}', 'Vendor_B' => '{}'],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_A']]
            ],
            [
                [['vendor/module-A', 'Vendor_A'], ['vendor/module-B', 'Vendor_B']],
                ['Vendor_A' => '{"conflict":{"vendor/module-B":0.1}}', 'Vendor_B' => '{}'],
                [],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['vendor/module-A', 'Vendor_A'], ['vendor/module-B', 'Vendor_B']],
                ['Vendor_B' => '{"conflict":{"vendor/module-A":0.1}}', 'Vendor_A' => '{}'],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_A']]
            ],
            [
                [['vendor/module-A', 'Vendor_A'], ['vendor/module-B', 'Vendor_B']],
                ['Vendor_B' => '{"conflict":{"vendor/module-A":0.1}}', 'Vendor_A' => '{}'],
                [],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['vendor/module-A', 'Vendor_A'], ['vendor/module-B', 'Vendor_B']],
                ['Vendor_A' => '{}', 'Vendor_B' => '{}'],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['vendor/module-A', 'Vendor_A'], ['vendor/module-B', 'Vendor_B'], ['vendor/module-C', 'Vendor_C']],
                ['Vendor_A' => '{}', 'Vendor_B' => '{}', 'Vendor_C' => '{}'],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                ['Vendor_B' => [], 'Vendor_C' => []]
            ],
            [
                [['vendor/module-A', 'Vendor_A'], ['vendor/module-B', 'Vendor_B'], ['vendor/module-C', 'Vendor_C']],
                ['Vendor_A' => '{"conflict":{"vendor/module-C":0.1}}', 'Vendor_B' => '{}', 'Vendor_C' => '{}'],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                ['Vendor_B' => [], 'Vendor_C' => ['Vendor_A']]
            ],
            [
                [['vendor/module-A', 'Vendor_A'], ['vendor/module-B', 'Vendor_B'], ['vendor/module-C', 'Vendor_C']],
                ['Vendor_A' => '{}', 'Vendor_B' => '{"conflict":{"vendor/module-C":0.1}}', 'Vendor_C' => '{}'],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                ['Vendor_B' => ['Vendor_C'], 'Vendor_C' => ['Vendor_B']]
            ],
        ];
    }
}
