<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Regenerates generated code and DI configuration
 */
class GeneratedFiles
{
    /**
     * Separator literal to assemble timer identifier from timer names
     */
    const REGENERATE_FLAG = '/var/.regenerate';

    /**
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var WriteInterface
     */
    private $writeInterface;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @param DriverPool $driverPool
     */
    public function __construct(DirectoryList $directoryList, DriverPool $driverPool) {
        $this->directoryList = $directoryList;
        $this->writeFactory = new WriteFactory($driverPool);
    }

    /**
     * Clean generated code and DI configuration
     *
     * @return void
     */
     public function requestRegeneration()
    {
        $this->writeInterface = $this->writeFactory->create(BP);

        if ($this->writeInterface->isExist(BP . self::REGENERATE_FLAG)) {
            $generationPath = BP . '/' . $this->directoryList->getPath(DirectoryList::GENERATION);
            $diPath = BP . '/' . $this->directoryList->getPath(DirectoryList::DI);

            if ($this->writeInterface->isDirectory($generationPath)) {
                $this->writeInterface->delete($generationPath);
			}
            if ($this->writeInterface->isDirectory($diPath)) {
            $this->writeInterface->delete($diPath);
            }
            $this->writeInterface->delete(BP . self::REGENERATE_FLAG);
        }
    }

    /**
     * Create flag for regeneration of code and di
     */
    public function createRequestForRegeneration()
    {
        $this->writeInterface->touch(BP . '/var/.regenerate');
    }
}
