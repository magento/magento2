<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron\Queue;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Queue content writer
 * @since 2.0.0
 */
class Writer extends Reader
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     * @since 2.0.0
     */
    private $writer;

    /**
     * Initialize reader.
     *
     * @param Filesystem $filesystem
     * @param string|null $queueFileBasename
     * @since 2.0.0
     */
    public function __construct(Filesystem $filesystem, $queueFileBasename = null)
    {
        $this->writer = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($filesystem, $queueFileBasename);
    }

    /**
     * Write JSON string into queue
     *
     * @param string $data
     * @return void
     * @since 2.0.0
     */
    public function write($data)
    {
        $this->writer->writeFile($this->queueFileBasename, $data);
    }
}
