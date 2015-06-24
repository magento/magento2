<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Phrase;
use Magento\Setup\Model\Updater;

class UpdaterTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateUpdaterTask()
    {
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $write = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface', [], '', false);
        $filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())->method('readFile')->willReturn('{"jobs":[{"name": "job A", "params": {}}]}');
        $rawData = [
            'jobs' => [
                ['name' => 'job A', 'params' => []],
                [
                    'name' => 'update',
                    'params' => ['require' => [['name' => 'vendor/package', 'version' => 'dev-master']]]
                ]
            ]
        ];
        $write->expects($this->once())
            ->method('writeFile')
            ->with('.update_queue.json', json_encode($rawData, JSON_PRETTY_PRINT));
        $updater = new Updater($filesystem);
        $updater->createUpdaterTask([['name' => 'vendor/package', 'version' => 'dev-master']]);
    }

    public function testCreateUpdaterTaskEmptyTask()
    {
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $write = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface', [], '', false);
        $filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())->method('readFile')->willThrowException(new FileSystemException(new Phrase('')));
        $rawData = [
            'jobs' => [
                [
                    'name' => 'update',
                    'params' => ['require' => [['name' => 'vendor/package', 'version' => 'dev-master']]]
                ]
            ]
        ];
        $write->expects($this->once())
            ->method('writeFile')
            ->with('.update_queue.json', json_encode($rawData, JSON_PRETTY_PRINT));
        $updater = new Updater($filesystem);
        $updater->createUpdaterTask([['name' => 'vendor/package', 'version' => 'dev-master']]);
    }
}
