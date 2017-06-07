<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\ObjectManagerProvider;
use \Magento\Setup\Model\UpdaterTaskCreator;

class UpdaterTaskCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\Updater|\PHPUnit_Framework_MockObject_MockObject
     */
    private $updater;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var Navigation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $navigation;

    /**
     * @var ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    public function setUp()
    {
        $this->updater = $this->getMock(\Magento\Setup\Model\Updater::class, [], [], '', false);
        $this->objectManagerProvider =
            $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $this->filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->navigation = $this->getMock(\Magento\Setup\Model\Navigation::class, [], [], '', false);
        $this->model = new UpdaterTaskCreator(
            $this->filesystem,
            $this->navigation,
            $this->updater,
            $this->objectManagerProvider
        );
        $this->navigation->expects($this->any())
            ->method('getMenuItems')
            ->willReturn([
                ['title' => 'A', 'type' => 'update'],
                ['title' => 'B', 'type' => 'upgrade'],
                ['title' => 'C', 'type' => 'enable'],
                ['title' => 'D', 'type' => 'disable'],
            ]);
    }

    /**
     * @param array $payload
     * @dataProvider createUpdaterTasksDataProvider
     */
    public function testCreateUpdaterTasks($payload)
    {
        $write = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class,
            [],
            '',
            false
        );
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())->method('writeFile');
        $cacheManager = $this->getMock(\Magento\Framework\App\Cache\Manager::class, [], [], '', false);
        $objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->expects($this->once())->method('get')->willReturn($cacheManager);
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $cacheManager->expects($this->once())->method('getStatus')->willReturn([
            'cache1' => 1, 'cache2' => 0, 'cache3' => 1
        ]);
        $this->model->createUpdaterTasks($payload);
    }

    /**
     * @return array
     */
    public function createUpdaterTasksDataProvider()
    {
        return [
            [['type' => 'uninstall',
                'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1']],
                'headerTitle'=>'Uninstall Package1', 'dataOption' => true
            ], 0, false],
            [['type' => 'update',
                'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1',]],
                'headerTitle'=>'Uninstall Package1'
            ], 0, false],
            [['type' => 'enable',
                'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1',]],
                'headerTitle'=>'Uninstall Package1'
            ], 1, true],
            [['type' => 'disable',
                'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1',]],
                'headerTitle'=>'Uninstall Package1'
            ], 1, true],
        ];
    }
}
