<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\Navigation;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\Updater;
use Magento\Setup\Model\UpdaterTaskCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdaterTaskCreatorTest extends TestCase
{
    /**
     * @var Updater|MockObject
     */
    private $updater;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var Navigation|MockObject
     */
    private $navigation;

    /**
     * @var ObjectManagerProvider|MockObject
     */
    private $objectManagerProvider;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(Updater::class);
        $this->objectManagerProvider =
            $this->createMock(ObjectManagerProvider::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->navigation = $this->createMock(Navigation::class);
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
            WriteInterface::class,
            [],
            '',
            false
        );
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())->method('writeFile');
        $cacheManager = $this->createMock(Manager::class);
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
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
                'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1']],
                'headerTitle'=>'Uninstall Package1'
            ], 0, false],
            [['type' => 'enable',
                'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1']],
                'headerTitle'=>'Uninstall Package1'
            ], 1, true],
            [['type' => 'disable',
                'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1']],
                'headerTitle'=>'Uninstall Package1'
            ], 1, true],
        ];
    }
}
