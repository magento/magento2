<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Cron\Queue;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Queue content file reader.
 */
class Reader
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $reader;

    /**
     * @var string
     */
    protected $queueFileBasename;

    /**
     * Initialize reader.
     *
     * @param Filesystem $filesystem
     * @param string|null $queueFileBasename
     */
    public function __construct(Filesystem $filesystem, $queueFileBasename = null)
    {
        $this->reader = $filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
        $this->queueFileBasename = $queueFileBasename ? $queueFileBasename : '.update_queue.json';
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
        if (!$this->reader->isExist($this->queueFileBasename)) {
            return $queue;
        }
        $queueFileContent = $this->reader->readFile($this->queueFileBasename);
        if ($queueFileContent) {
            json_decode($queueFileContent);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException(sprintf('Content of "%s" must be a valid JSON.', $this->queueFileBasename));
            }
            $queue = $queueFileContent;
        }
        return $queue;
    }
}
