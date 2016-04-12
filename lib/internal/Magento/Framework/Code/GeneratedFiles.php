<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

use Magento\Framework\App\Filesystem\DirectoryList;
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
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var WriteInterface
     */
    private $write;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @param WriteFactory $writeFactory
     */
    public function __construct(DirectoryList $directoryList, WriteFactory $writeFactory)
    {
        $this->directoryList = $directoryList;
        $this->write = $writeFactory->create(BP);
    }

    /**
     * Clean generated code and DI configuration
     *
     * @return void
     */
    public function regenerate()
    {
        if ($this->write->isExist(self::REGENERATE_FLAG)) {
            $generationPath = BP . '/' . $this->directoryList->getPath(DirectoryList::GENERATION);
            $diPath = BP . '/' . $this->directoryList->getPath(DirectoryList::DI);

            if ($this->write->isDirectory($generationPath)) {
                $this->write->delete($generationPath);
            }
            if ($this->write->isDirectory($diPath)) {
                $this->write->delete($diPath);
            }
            $this->write->delete(BP . self::REGENERATE_FLAG);
        }
    }

    /**
     * Create flag for regeneration of code and di
     *
     * @return void
    */
    public function requestRegeneration()
    {
        $this->write->touch(self::REGENERATE_FLAG);
    }
}
