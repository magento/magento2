<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Logger\Handler;

use Magento\Framework\Filesystem\Driver\File;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class System extends StreamHandler
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/system.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * @var File
     */
    protected $filesystem;

    /**
     * @param File $filesystem
     */
    public function __construct(File $filesystem)
    {
        $this->filesystem = $filesystem;
        parent::__construct(BP . $this->fileName, $this->loggerType);
    }

    /**
     * @{inerhitDoc}
     *
     * @param $record array
     */
    public function write(array $record)
    {
        $logDir = $this->filesystem->getParentDirectory($this->url);
        if (!$this->filesystem->isDirectory($logDir)) {
            $this->filesystem->createDirectory($logDir, 0777);
        }
        parent::write($record);
    }
}
