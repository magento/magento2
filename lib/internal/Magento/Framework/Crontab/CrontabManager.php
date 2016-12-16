<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Crontab;

use Magento\Framework\ShellInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Manager works with cron tasks
 */
class CrontabManager implements CrontabManagerInterface
{
    /**
     * @var ShellInterface
     */
    private $shell;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param ShellInterface $shell
     * @param Filesystem $filesystem
     */
    public function __construct(
        ShellInterface $shell,
        Filesystem $filesystem
    ) {
        $this->shell = $shell;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function getTasks()
    {
        $content = $this->getCrontabContent();
        $pattern = '!(' . self::TASKS_BLOCK_START . ')(.*?)(' . self::TASKS_BLOCK_END . ')!s';

        if (preg_match($pattern, $content, $matches)) {
            $tasks = trim($matches[2], PHP_EOL);
            $tasks = explode(PHP_EOL, $tasks);
            return $tasks;
        }

        return [];
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function saveTasks(array $tasks)
    {
        $baseDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        $logDir = $this->filesystem->getDirectoryRead(DirectoryList::LOG)->getAbsolutePath();

        if (!$tasks) {
            throw new LocalizedException(new Phrase('List of tasks is empty'));
        }

        foreach ($tasks as $key => $task) {
            if (empty($task['expression'])) {
                $tasks[$key]['expression'] = '* * * * *';
            }

            if (empty($task['command'])) {
                throw new LocalizedException(new Phrase('Command should not be empty'));
            }

            $tasks[$key]['command'] = str_replace(
                ['{magentoRoot}', '{magentoLog}'],
                [$baseDir, $logDir],
                $task['command']
            );
        }

        $content = $this->getCrontabContent();
        $content = $this->cleanMagentoSection($content);
        $content = $this->generateSection($content, $tasks);

        $this->save($content);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function removeTasks()
    {
        $content = $this->getCrontabContent();
        $content = $this->cleanMagentoSection($content);
        $this->save($content);
    }

    /**
     * Generate Magento Tasks Section
     *
     * @param string $content
     * @param array $tasks
     * @return string
     */
    private function generateSection($content, $tasks = [])
    {
        if ($tasks) {
            $content .= self::TASKS_BLOCK_START . PHP_EOL;
            foreach ($tasks as $task) {
                $content .=  $task['expression'] . ' ' . PHP_BINARY . ' '. $task['command'] . PHP_EOL;
            }
            $content .= self::TASKS_BLOCK_END . PHP_EOL;
        }

        return $content;
    }

    /**
     * Clean Magento Tasks Section in crontab content
     *
     * @param string $content
     * @return string
     */
    private function cleanMagentoSection($content)
    {
        $content = preg_replace('!' . self::TASKS_BLOCK_START . '.*?' . self::TASKS_BLOCK_END . '!s', '', $content);
        $content = preg_replace('/\n\s*\n/', "\n", $content);

        return $content;
    }

    /**
     * Get crontab content without Magento Tasks Section
     *
     * @return string
     * @throws \Exception
     */
    private function getCrontabContent()
    {
        try {
            $content = (string)$this->shell->execute('crontab -l');
        } catch (LocalizedException $e) {
            if (strpos($e->getPrevious()->getMessage(), 'no crontab') !== false) {
                return '';
            }

            throw $e->getPrevious();
        }

        return $content;
    }

    /**
     * Save crontab
     *
     * @param string $content
     * @return void
     * @throws \Exception
     */
    private function save($content)
    {
        $content = str_replace('%', '%%', $content);

        try {
            $this->shell->execute('echo "' . $content . '" | crontab -');
        } catch (LocalizedException $e) {
            throw $e->getPrevious();
        }
    }
}
