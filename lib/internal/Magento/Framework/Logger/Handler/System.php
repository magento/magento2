<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
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
     * @var DriverInterface
     */
    protected $filesystem;

    /**
     * @param DriverInterface $filesystem
     */
    public function __construct(DriverInterface $filesystem)
    {
        $this->filesystem = $filesystem;
        parent::__construct(BP . $this->fileName, $this->loggerType);
    }

    /**
     * @{inerhitDoc}
     *
     * @param $record array
     * @return void
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
