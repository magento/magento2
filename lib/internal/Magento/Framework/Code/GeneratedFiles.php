<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Driver\File;

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
     * @var ReadInterface
     */
    private $readInterface;

    /**
     * @var File
     */
    private $file;

    /**
     * Constructor
     *
     * @param ReadInterface $readInterface
     * @param File $file
     */
    public function __construct(ReadInterface $readInterface, File $file) {
        $this->readInterface = $readInterface;
        $this->file = $file;
    }

    /**
     * Clean generated code and DI configuration
     *
     * @param array $initParams
     * @return void
     */
     public function requestRegeneration($initParams)
    {
        if (file_exists(BP . self::REGENERATE_FLAG)) {
            $directoryList = new DirectoryList(BP, $initParams);
            $generationPath = BP . '/' . $directoryList->getPath(DirectoryList::GENERATION);
            $diPath = BP . '/' . $directoryList->getPath(DirectoryList::DI);

            if ($this->readInterface->isDirectory($generationPath)) {
                $this->file->deleteDirectory($generationPath, true);
			}
            if ($this->readInterface->isDirectory($diPath)) {
                $this->file->deleteDirectory($diPath, true);
            }
            unlink(BP . self::REGENERATE_FLAG);
        }
    }

    /**
     * Create flag for regeneration of code and di
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createRequestForRegeneration()
    {
        $this->file->touch(BP . '/var/.regenerate');
    }
}
