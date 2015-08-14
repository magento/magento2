<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Css\PreProcessor;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\SourceFileGeneratorInterface;

/**
 * Class FileGenerator
 * @package Magento\Framework\Css
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
    const LOCK_FILE = 'css.lock';

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
     * @var \Magento\Framework\Css\PreProcessor\Instruction\MagentoImport
     */
    private $magentoImportProcessor;

    /**
     * @var \Magento\Framework\Css\PreProcessor\Instruction\Import
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
     * @param \Magento\Framework\Css\PreProcessor\Instruction\MagentoImport $magentoImportProcessor
     * @param \Magento\Framework\Css\PreProcessor\Instruction\Import $importProcessor
     * @param \Magento\Framework\View\Asset\Source $assetSource
     * @param FileGenerator\RelatedGenerator $relatedGenerator
     * @param Config $config
     * @param File\Temporary $temporaryFile
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Css\PreProcessor\Instruction\MagentoImport $magentoImportProcessor,
        \Magento\Framework\Css\PreProcessor\Instruction\Import $importProcessor,
        \Magento\Framework\View\Asset\Source $assetSource,
        \Magento\Framework\Css\PreProcessor\FileGenerator\RelatedGenerator $relatedGenerator,
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
     * Create a tree of self-sustainable files and return the topmost source file,
     * ready for passing to 3rd party library
     *
     * @param Chain $chain
     * @return string Absolute path of generated topmost source file
     */
    public function generateFileTree(Chain $chain)
    {
        /**
         * wait if generation process has already started
         */
        while ($this->isProcessLocked()) {
            sleep(1);
        }
        $lockFilePath = $this->config->getMaterializationRelativePath() . '/' . self::LOCK_FILE;
        $this->tmpDirectory->writeFile($lockFilePath, time());

        $this->magentoImportProcessor->process($chain);
        $this->importProcessor->process($chain);
        $this->relatedGenerator->generate($this->importProcessor);

        $contentType = $chain->getContentType();
        $relativePath = preg_replace('#\.css$#', '.' . $contentType, $chain->getAsset()->getPath());
        $tmpFilePath = $this->temporaryFile->createFile($relativePath, $chain->getContent());

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
        $lockFilePath = $this->config->getMaterializationRelativePath() . '/' . self::LOCK_FILE;
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
