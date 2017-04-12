<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Crontab\Test\Unit;

use Magento\Framework\Crontab\CrontabManager;
use Magento\Framework\Crontab\CrontabManagerInterface;
use Magento\Framework\ShellInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverPool;

class CrontabManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShellInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shellMock;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var CrontabManager
     */
    private $crontabManager;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->shellMock = $this->getMockBuilder(ShellInterface::class)
            ->getMockForAbstractClass();
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalClone()
            ->disableOriginalConstructor()
            ->getMock();

        $this->crontabManager = new CrontabManager($this->shellMock, $this->filesystemMock);
    }

    /**
     * @return void
     */
    public function testGetTasksNoCrontab()
    {
        $exception = new \Exception('crontab: no crontab for user');
        $localizedException = new LocalizedException(new Phrase('Some error'), $exception);

        $this->shellMock->expects($this->once())
            ->method('execute')
            ->with('crontab -l', [])
            ->willThrowException($localizedException);

        $this->assertEquals([], $this->crontabManager->getTasks());
    }

    /**
     * @param string $content
     * @param array $tasks
     * @return void
     * @dataProvider getTasksDataProvider
     */
    public function testGetTasks($content, $tasks)
    {
        $this->shellMock->expects($this->once())
            ->method('execute')
            ->with('crontab -l', [])
            ->willReturn($content);

        $this->assertEquals($tasks, $this->crontabManager->getTasks());
    }

    /**
     * @return array
     */
    public function getTasksDataProvider()
    {
        return [
            [
                'content' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . PHP_EOL
                    . '* * * * * /bin/php /var/www/magento/bin/magento cron:run' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . PHP_EOL,
                'tasks' => ['* * * * * /bin/php /var/www/magento/bin/magento cron:run'],
            ],
            [
                'content' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . PHP_EOL
                    . '* * * * * /bin/php /var/www/magento/bin/magento cron:run' . PHP_EOL
                    . '* * * * * /bin/php /var/www/magento/bin/magento setup:cron:run' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . PHP_EOL,
                'tasks' => [
                    '* * * * * /bin/php /var/www/magento/bin/magento cron:run',
                    '* * * * * /bin/php /var/www/magento/bin/magento setup:cron:run',
                ],
            ],
            [
                'content' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL,
                'tasks' => [],
            ],
            [
                'content' => '',
                'tasks' => [],
            ],
        ];
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Shell error
     */
    public function testRemoveTasksWithException()
    {
        $exception = new \Exception('Shell error');
        $localizedException = new LocalizedException(new Phrase('Some error'), $exception);

        $this->shellMock->expects($this->at(0))
            ->method('execute')
            ->with('crontab -l', [])
            ->willReturn('');

        $this->shellMock->expects($this->at(1))
            ->method('execute')
            ->with('echo "" | crontab -', [])
            ->willThrowException($localizedException);

        $this->crontabManager->removeTasks();
    }

    /**
     * @param string $contentBefore
     * @param string $contentAfter
     * @return void
     * @dataProvider removeTasksDataProvider
     */
    public function testRemoveTasks($contentBefore, $contentAfter)
    {
        $this->shellMock->expects($this->at(0))
            ->method('execute')
            ->with('crontab -l', [])
            ->willReturn($contentBefore);

        $this->shellMock->expects($this->at(1))
            ->method('execute')
            ->with('echo "' . $contentAfter . '" | crontab -', []);

        $this->crontabManager->removeTasks();
    }

    /**
     * @return array
     */
    public function removeTasksDataProvider()
    {
        return [
            [
                'contentBefore' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . PHP_EOL
                    . '* * * * * /bin/php /var/www/magento/bin/magento cron:run' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . PHP_EOL,
                'contentAfter' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
            ],
            [
                'contentBefore' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . PHP_EOL
                    . '* * * * * /bin/php /var/www/magento/bin/magento cron:run' . PHP_EOL
                    . '* * * * * /bin/php /var/www/magento/bin/magento setup:cron:run' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . PHP_EOL,
                'contentAfter' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
            ],
            [
                'contentBefore' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL,
                'contentAfter' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
            ],
            [
                'contentBefore' => '',
                'contentAfter' => ''
            ],
        ];
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage List of tasks is empty
     */
    public function testSaveTasksWithEmptyTasksList()
    {
        $baseDirMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $baseDirMock->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('/var/www/magento2/');
        $logDirMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $logDirMock->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('/var/www/magento2/var/log/');

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturnMap([
                [DirectoryList::ROOT, DriverPool::FILE, $baseDirMock],
                [DirectoryList::LOG, DriverPool::FILE, $logDirMock],
            ]);

        $this->crontabManager->saveTasks([]);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Command should not be empty
     */
    public function testSaveTasksWithoutCommand()
    {
        $baseDirMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $baseDirMock->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('/var/www/magento2/');
        $logDirMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $logDirMock->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('/var/www/magento2/var/log/');

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturnMap([
                [DirectoryList::ROOT, DriverPool::FILE, $baseDirMock],
                [DirectoryList::LOG, DriverPool::FILE, $logDirMock],
            ]);

        $this->crontabManager->saveTasks([
            'myCron' => ['expression' => '* * * * *']
        ]);
    }

    /**
     * @param array $tasks
     * @param string $content
     * @param string $contentToSave
     * @return void
     * @dataProvider saveTasksDataProvider
     */
    public function testSaveTasks($tasks, $content, $contentToSave)
    {
        $baseDirMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $baseDirMock->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('/var/www/magento2/');
        $logDirMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $logDirMock->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('/var/www/magento2/var/log/');

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturnMap([
                [DirectoryList::ROOT, DriverPool::FILE, $baseDirMock],
                [DirectoryList::LOG, DriverPool::FILE, $logDirMock],
            ]);

        $this->shellMock->expects($this->at(0))
            ->method('execute')
            ->with('crontab -l', [])
            ->willReturn($content);

        $this->shellMock->expects($this->at(1))
            ->method('execute')
            ->with('echo "' . $contentToSave . '" | crontab -', []);

        $this->crontabManager->saveTasks($tasks);
    }

    /**
     * @return array
     */
    public function saveTasksDataProvider()
    {
        $content = '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
            . CrontabManagerInterface::TASKS_BLOCK_START . PHP_EOL
            . '* * * * * /bin/php /var/www/magento/bin/magento cron:run' . PHP_EOL
            . CrontabManagerInterface::TASKS_BLOCK_END . PHP_EOL;

        return [
            [
                'tasks' => [
                    ['expression' => '* * * * *', 'command' => 'run.php']
                ],
                'content' => $content,
                'contentToSave' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . PHP_EOL
                    . '* * * * * ' . PHP_BINARY . ' run.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . PHP_EOL,
            ],
            [
                'tasks' => [
                    ['expression' => '1 2 3 4 5', 'command' => 'run.php']
                ],
                'content' => $content,
                'contentToSave' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . PHP_EOL
                    . '1 2 3 4 5 ' . PHP_BINARY . ' run.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . PHP_EOL,
            ],
            [
                'tasks' => [
                    ['command' => '{magentoRoot}run.php >> {magentoLog}cron.log']
                ],
                'content' => $content,
                'contentToSave' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . PHP_EOL
                    . '* * * * * ' . PHP_BINARY . ' /var/www/magento2/run.php >>'
                    . ' /var/www/magento2/var/log/cron.log' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . PHP_EOL,
            ],
        ];
    }
}
