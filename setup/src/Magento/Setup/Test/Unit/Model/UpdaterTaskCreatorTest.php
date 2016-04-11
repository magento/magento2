<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Framework\App\Cache\Manager;
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
     * @var Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManager;

    public function setUp()
    {
        $this->updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        $this->cacheManager = $this->getMock('\Magento\Framework\App\Cache\Manager', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->navigation = $this->getMock('Magento\Setup\Model\Navigation', [], [], '', false);
        $this->model = new UpdaterTaskCreator(
            $this->filesystem,
            $this->navigation,
            $this->updater,
            $this->cacheManager
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
     * @dataProvider createUpdaterTasksDataProvider
     */
    public function testCreateUpdaterTasks($payload, $one, $two)
    {
        $write = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface', [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())
            ->method('writeFile');
            //->with('.type.json', '{"type":"enable","headerTitle":"Enable Package 1","titles":["C"]}');
        $this->cacheManager->expects($this->once())->method('getStatus')->willReturn([
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
            [['type' => 'uninstall', 'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1']], 'headerTitle'=>'Uninstall Package1', 'dataOption' => true], 0, false],
            [['type' => 'update', 'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1',]], 'headerTitle'=>'Uninstall Package1'], 0, false],
            [['type' => 'enable', 'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1',]], 'headerTitle'=>'Uninstall Package1'], 1, true],
            [['type' => 'disable', 'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1',]], 'headerTitle'=>'Uninstall Package1'], 1, true],
        ];
    }

}
