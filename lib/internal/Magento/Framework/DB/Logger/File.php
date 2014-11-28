<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function logException(\Exception $e)
    {
        $this->log("EXCEPTION \n$e\n\n");
    }
}
