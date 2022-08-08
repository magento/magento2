<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Crontab\Test\Unit;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Crontab\CrontabManager;
use Magento\Framework\Crontab\CrontabManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Phrase;
use Magento\Framework\ShellInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests crontab manager functionality.
 */
class CrontabManagerTest extends TestCase
{
    /**
     * @var ShellInterface|MockObject
     */
    private $shellMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var CrontabManager
     */
    private $crontabManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
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
     * Verify get tasks without cronetab.
     *
     * @return void
     */
    public function testGetTasksNoCrontab(): void
    {
        $exception = new Exception('crontab: no crontab for user');
        $localizedException = new LocalizedException(new Phrase('Some error'), $exception);

        $this->shellMock->expects($this->once())
            ->method('execute')
            ->with('crontab -l 2>/dev/null', [])
            ->willThrowException($localizedException);

        $this->assertEquals([], $this->crontabManager->getTasks());
    }

    /**
     * Verify get tasks.
     *
     * @param string $content
     * @param array $tasks
     *
     * @return void
     * @dataProvider getTasksDataProvider
     */
    public function testGetTasks($content, $tasks): void
    {
        $this->shellMock->expects($this->once())
            ->method('execute')
            ->with('crontab -l 2>/dev/null', [])
            ->willReturn($content);

        $this->assertEquals($tasks, $this->crontabManager->getTasks());
    }

    /**
     * Data provider to get tasks.
     *
     * @return array
     */
    public function getTasksDataProvider(): array
    {
        return [
            [
                'content' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . ' ' . hash("sha256", BP) . PHP_EOL
                    . '* * * * * /bin/php /var/www/magento/bin/magento cron:run' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . ' ' . hash("sha256", BP) . PHP_EOL,
                'tasks' => ['* * * * * /bin/php /var/www/magento/bin/magento cron:run']
            ],
            [
                'content' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . ' ' . hash("sha256", BP) . PHP_EOL
                    . '* * * * * /bin/php /var/www/magento/bin/magento cron:run' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . ' ' . hash("sha256", BP) . PHP_EOL,
                'tasks' => [
                    '* * * * * /bin/php /var/www/magento/bin/magento cron:run'
                ]
            ],
            [
                'content' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL,
                'tasks' => []
            ],
            [
                'content' => '',
                'tasks' => []
            ]
        ];
    }

    /**
     * Verify remove tasks with exception.
     *
     * @return void
     */
    public function testRemoveTasksWithException(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Shell error');
        $exception = new Exception('Shell error');
        $localizedException = new LocalizedException(new Phrase('Some error'), $exception);

        $this->shellMock
            ->method('execute')
            ->withConsecutive(['crontab -l 2>/dev/null', []], ['echo "" | crontab -', []])
            ->willReturnOnConsecutiveCalls('', $this->throwException($localizedException));

        $this->crontabManager->removeTasks();
    }

    /**
     * Verify remove tasks.
     *
     * @param string $contentBefore
     * @param string $contentAfter
     *
     * @return void
     * @dataProvider removeTasksDataProvider
     */
    public function testRemoveTasks($contentBefore, $contentAfter): void
    {
        $this->shellMock
            ->method('execute')
            ->withConsecutive(['crontab -l 2>/dev/null', []], ['echo "' . $contentAfter . '" | crontab -', []])
            ->willReturn($contentBefore);

        $this->crontabManager->removeTasks();
    }

    /**
     * Data provider to remove tasks.
     *
     * @return array
     */
    public function removeTasksDataProvider(): array
    {
        return [
            [
                'contentBefore' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . ' ' . hash("sha256", BP) . PHP_EOL
                    . '* * * * * /bin/php /var/www/magento/bin/magento cron:run' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . ' ' . hash("sha256", BP) . PHP_EOL,
                'contentAfter' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
            ],
            [
                'contentBefore' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . ' ' . hash("sha256", BP) . PHP_EOL
                    . '* * * * * /bin/php /var/www/magento/bin/magento cron:run' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . ' ' . hash("sha256", BP) . PHP_EOL,
                'contentAfter' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
            ],
            [
                'contentBefore' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL,
                'contentAfter' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
            ],
            [
                'contentBefore' => '',
                'contentAfter' => ''
            ]
        ];
    }

    /**
     * Verify save tasks with empty tasks list.
     *
     * @return void
     */
    public function testSaveTasksWithEmptyTasksList(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The list of tasks is empty. Add tasks and try again.');
        $baseDirMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $baseDirMock->expects($this->never())
            ->method('getAbsolutePath')
            ->willReturn('/var/www/magento2/');
        $logDirMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $logDirMock->expects($this->never())
            ->method('getAbsolutePath');

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturnMap([
                [DirectoryList::ROOT, DriverPool::FILE, $baseDirMock],
                [DirectoryList::LOG, DriverPool::FILE, $logDirMock],
            ]);

        $this->crontabManager->saveTasks([]);
    }

    /**
     * Verify save tasks with out command.
     *
     * @return void
     */
    public function testSaveTasksWithoutCommand(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The command shouldn\'t be empty. Enter and try again.');
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
     * Verify sava task.
     *
     * @param array $tasks
     * @param string $content
     * @param string $contentToSave
     *
     * @return void
     * @dataProvider saveTasksDataProvider
     */
    public function testSaveTasks($tasks, $content, $contentToSave): void
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

        $this->shellMock
            ->method('execute')
            ->withConsecutive(['crontab -l 2>/dev/null', []], ['echo "' . $contentToSave . '" | crontab -', []])
            ->willReturn($content);

        $this->crontabManager->saveTasks($tasks);
    }

    /**
     * Data provider to save tasks.
     *
     * @return array
     */
    public function saveTasksDataProvider(): array
    {
        $content = '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
            . CrontabManagerInterface::TASKS_BLOCK_START . ' ' . hash("sha256", BP) . PHP_EOL
            . '* * * * * /bin/php /var/www/magento/bin/magento cron:run' . PHP_EOL
            . CrontabManagerInterface::TASKS_BLOCK_END . ' ' . hash("sha256", BP) . PHP_EOL;

        return [
            [
                'tasks' => [
                    ['expression' => '* * * * *', 'command' => 'run.php']
                ],
                'content' => $content,
                'contentToSave' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . ' ' . hash("sha256", BP) . PHP_EOL
                    . '* * * * * ' . PHP_BINARY . ' run.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . ' ' . hash("sha256", BP) . PHP_EOL
            ],
            [
                'tasks' => [
                    ['expression' => '1 2 3 4 5', 'command' => 'run.php']
                ],
                'content' => $content,
                'contentToSave' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . ' ' . hash("sha256", BP) . PHP_EOL
                    . '1 2 3 4 5 ' . PHP_BINARY . ' run.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . ' ' . hash("sha256", BP) . PHP_EOL
            ],
            [
                'tasks' => [
                    ['command' => '{magentoRoot}run.php >> {magentoLog}cron.log']
                ],
                'content' => $content,
                'contentToSave' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . ' ' . hash("sha256", BP) . PHP_EOL
                    . '* * * * * ' . PHP_BINARY . ' /var/www/magento2/run.php >>'
                    . ' /var/www/magento2/var/log/cron.log' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . ' ' . hash("sha256", BP) . PHP_EOL
            ],
            [
                'tasks' => [
                    ['command' => '{magentoRoot}run.php % cron:run | grep -v "Ran \'jobs\' by schedule"']
                ],
                'content' => $content,
                'contentToSave' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . ' ' . hash("sha256", BP) . PHP_EOL
                    . '* * * * * ' . PHP_BINARY . ' /var/www/magento2/run.php'
                    . ' %% cron:run | grep -v \"Ran \'jobs\' by schedule\"' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . ' ' . hash("sha256", BP) . PHP_EOL
            ],
            [
                'tasks' => [
                    ['command' => '{magentoRoot}run.php % cron:run | grep -v "Ran \'jobs\' by schedule"']
                ],
                'content' => '* * * * * /bin/php /var/www/cron.php',
                'contentToSave' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . ' ' . hash("sha256", BP) . PHP_EOL
                    . '* * * * * ' . PHP_BINARY . ' /var/www/magento2/run.php'
                    . ' %% cron:run | grep -v \"Ran \'jobs\' by schedule\"' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . ' ' . hash("sha256", BP) . PHP_EOL
            ],
            [
                'tasks' => [
                    ['command' => '{magentoRoot}run.php mysqldump --no-tablespaces db > db-$(date +%F).sql']
                ],
                'content' => '* * * * * /bin/php /var/www/cron.php',
                'contentToSave' => '* * * * * /bin/php /var/www/cron.php' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_START . ' ' . hash("sha256", BP) . PHP_EOL
                    . '* * * * * ' . PHP_BINARY . ' /var/www/magento2/run.php'
                    . ' mysqldump --no-tablespaces db > db-\$(date +%%F).sql' . PHP_EOL
                    . CrontabManagerInterface::TASKS_BLOCK_END . ' ' . hash("sha256", BP) . PHP_EOL
            ]
        ];
    }
}
