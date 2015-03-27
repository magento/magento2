<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Less;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\SourceFileGeneratorInterface;

/**
 * Class FileGenerator
 * @package Magento\Framework\Less
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileGenerator implements SourceFileGeneratorInterface
{
    /**
     * Max execution (locking) time for generation process (in seconds)
     */
    const MAX_LOCK_TIME = 300;

    /**
     * Lock file, if exists shows that process is locked
     */
    const LOCK_FILE = 'less.lock';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $tmpDirectory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\View\Asset\Source
     */
    private $assetSource;

    /**
     * @var \Magento\Framework\Less\PreProcessor\Instruction\MagentoImport
     */
    private $magentoImportProcessor;

    /**
     * @var \Magento\Framework\Less\PreProcessor\Instruction\Import
     */
    private $importProcessor;

    /**
     * @var FileGenerator\RelatedGenerator
     */
    private $relatedGenerator;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var File\Temporary
     */
    private $temporaryFile;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param PreProcessor\Instruction\MagentoImport $magentoImportProcessor
     * @param PreProcessor\Instruction\Import $importProcessor
     * @param \Magento\Framework\View\Asset\Source $assetSource
     * @param FileGenerator\RelatedGenerator $relatedGenerator
     * @param Config $config
     * @param File\Temporary $temporaryFile
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Less\PreProcessor\Instruction\MagentoImport $magentoImportProcessor,
        \Magento\Framework\Less\PreProcessor\Instruction\Import $importProcessor,
        \Magento\Framework\View\Asset\Source $assetSource,
        \Magento\Framework\Less\FileGenerator\RelatedGenerator $relatedGenerator,
        Config $config,
        File\Temporary $temporaryFile
    ) {
        $this->tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->assetRepo = $assetRepo;
        $this->assetSource = $assetSource;

        $this->magentoImportProcessor = $magentoImportProcessor;
        $this->importProcessor = $importProcessor;
        $this->relatedGenerator = $relatedGenerator;
        $this->config = $config;
        $this->temporaryFile = $temporaryFile;
    }

    /**
     * Create a tree of self-sustainable files and return the topmost LESS file, ready for passing to 3rd party library
     *
     * @param Chain $chain
     * @return string Absolute path of generated LESS file
     */
    public function generateFileTree(Chain $chain)
    {
        /**
         * @bug This logic is duplicated at \Magento\Framework\View\Asset\PreProcessor\Pool
         * If you need to extend or modify behavior of LESS preprocessing, you must account for both places
         */

        /**
         * wait if generation process has already started
         */
        while ($this->isProcessLocked()) {
            sleep(1);
        }
        $lockFilePath = $this->config->getLessMaterializationRelativePath() . '/' . self::LOCK_FILE;
        $this->tmpDirectory->writeFile($lockFilePath, time());

        $this->magentoImportProcessor->process($chain);
        $this->importProcessor->process($chain);
        $this->relatedGenerator->generate($this->importProcessor);
        $lessRelativePath = preg_replace('#\.css$#', '.less', $chain->getAsset()->getPath());
        $tmpFilePath = $this->temporaryFile->createFile($lessRelativePath, $chain->getContent());

        $this->tmpDirectory->delete($lockFilePath);
        return $tmpFilePath;
    }

    /**
     * Check whether generation process has already locked
     *
     * @return bool
     */
    protected function isProcessLocked()
    {
        $lockFilePath = $this->config->getLessMaterializationRelativePath() . '/' . self::LOCK_FILE;
        if ($this->tmpDirectory->isExist($lockFilePath)) {
            $lockTime = time() - (int)$this->tmpDirectory->readFile($lockFilePath);
            if ($lockTime >= self::MAX_LOCK_TIME) {
                $this->tmpDirectory->delete($lockFilePath);
                return false;
            }
            return true;
        }
        return false;
    }
}
