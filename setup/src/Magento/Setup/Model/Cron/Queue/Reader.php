<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron\Queue;

/**
 * Queue content file reader.
 */
class Reader
{
    /**
     * @var string
     */
    protected $queueFilePath;

    /**
     * Initialize reader.
     *
     * @param string|null $queueFilePath
     */
    public function __construct($queueFilePath = null)
    {
        $this->queueFilePath = $queueFilePath ? $queueFilePath : BP . '/var/.update_queue.json';
    }

    /**
     * Read Magento updater application jobs queue as a JSON string.
     *
     * @return string Queue file content (valid JSON string)
     * @throws \RuntimeException
     */
    public function read()
    {
        $queue = '';
        if (!file_exists($this->queueFilePath)) {
            return $queue;
        }
        $queueFileContent = file_get_contents($this->queueFilePath);
        if ($queueFileContent) {
            json_decode($queueFileContent);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException(sprintf('Content of "%s" must a valid JSON.', $this->queueFilePath));
            }
            $queue = $queueFileContent;
        }
        return $queue;
    }

    /**
     * Clear content of the Magento updater application jobs queue file.
     *
     * @return void
     * @throws \RuntimeException If queue file exists but cannot be cleared
     */
    public function clearQueue()
    {
        if (file_exists($this->queueFilePath)) {
            $isClearedSuccessfully = (false !== file_put_contents($this->queueFilePath, ''));
            if (!$isClearedSuccessfully) {
                throw new \RuntimeException(
                    sprintf('Magento updater application jobs queue file "%s" cannot be cleared.', $this->queueFilePath)
                );
            }
        }
    }
}
