<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Logger;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Logging to file
 */
class File extends LoggerAbstract
{
    /**
     * @var WriteInterface
     */
    private $dir;

    /**
     * Path to SQL debug data log
     *
     * @var string
     */
    protected $debugFile;

    /**
     * @param Filesystem $filesystem
     * @param string $debugFile
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     */
    public function __construct(
        Filesystem $filesystem,
        $debugFile = 'debug/db.log',
        $logAllQueries = false,
        $logQueryTime = 0.05,
        $logCallStack = false
    ) {
        parent::__construct($logAllQueries, $logQueryTime, $logCallStack);
        $this->dir = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->debugFile = $debugFile;
    }

    /**
     * {@inheritdoc}
     */
    public function log($str)
    {
        $str = '## ' . date('Y-m-d H:i:s') . "\r\n" . $str;

        $stream = $this->dir->openFile($this->debugFile, 'a');
        $stream->lock();
        $stream->write($str);
        $stream->unlock();
        $stream->close();
    }

    /**
     * {@inheritdoc}
     */
    public function logStats($type, $sql, $bind = [], $result = null)
    {
        $stats = $this->getStats($type, $sql, $bind, $result);
        if ($stats) {
            $this->log($stats);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function critical(\Exception $e)
    {
        $this->log("EXCEPTION \n$e\n\n");
    }
}
