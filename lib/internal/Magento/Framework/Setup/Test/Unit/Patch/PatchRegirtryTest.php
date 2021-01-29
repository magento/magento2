<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit\Patch;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Patch\PatchFactory;
use Magento\Framework\Setup\Patch\PatchHistory;
use Magento\Framework\Setup\Patch\PatchRegistry;

/**
 * Class PatchRegirtryTest
 * @package Magento\Framework\Setup\Test\Unit\Patch
 */
class PatchRegirtryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PatchRegistry
     */
    private $patchRegistry;

    /**
     * @var PatchFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $patchFactoryMock;

    /**
     * @var PatchHistory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $patchHistoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->patchFactoryMock = $this->getMockBuilder(PatchFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->patchHistoryMock = $this->getMockBuilder(PatchHistory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->patchRegistry = $objectManager->getObject(
            PatchRegistry::class,
            [
                'patchHistory' => $this->patchHistoryMock,
                'patchFactory' => $this->patchFactoryMock,
            ]
        );
        require_once __DIR__ . '/../_files/data_patch_classes.php';
    }

    public function testRegisterAppliedPatch()
    {
        $this->patchHistoryMock->expects($this->once())
            ->method('isApplied')
            ->with(\SomeDataPatch::class)
            ->willReturn(false);

        $this->assertEquals(\SomeDataPatch::class, $this->patchRegistry->registerPatch(\SomeDataPatch::class));
    }

    public function testRegisterNonAplliedPatch()
    {
        $this->patchHistoryMock->expects($this->once())
            ->method('isApplied')
            ->with(\SomeDataPatch::class)
            ->willReturn(true);

        $this->assertFalse($this->patchRegistry->registerPatch(\SomeDataPatch::class));
    }

    public function testGetIterator()
    {
        $this->patchHistoryMock->expects($this->any())
            ->method('isApplied')
            ->willReturnMap(
                [
                    [\SomeDataPatch::class, false],
                    [\OtherDataPatch::class, false]
                ]
            );

        $this->assertEquals(\SomeDataPatch::class, $this->patchRegistry->registerPatch(\SomeDataPatch::class));

        $actualPatches = [];
        foreach ($this->patchRegistry->getIterator() as $patch) {
            $actualPatches[] = $patch;
        }
        // assert that all dependencies are present and placed in valid sequence
        $this->assertEquals(
            [\OtherDataPatch::class, \SomeDataPatch::class],
            $actualPatches,
            'Failed to assert that actual non-apllied patches sequence is valid.'
        );
    }
}
