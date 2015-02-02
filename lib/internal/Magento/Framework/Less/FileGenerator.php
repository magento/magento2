<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Less;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Source;

class FileGenerator
{
    /**
     * Temporary directory prefix
     */
    const TMP_LESS_DIR = 'less';

    /**
     * Max execution (locking) time for generation process (in seconds)
     */
    const MAX_LOCK_TIME = 300;

    /**
     * Lock file, if exists shows that process is locked
     */
    const LOCK_FILE = 'less.lock';

    /**
     * @var string
     */
    protected $lessDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $tmpDirectory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\Less\PreProcessor\Instruction\MagentoImport
     */
    private $magentoImportProcessor;

    /**
     * @var \Magento\Framework\Less\PreProcessor\Instruction\Import
     */
    private $importProcessor;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Less\PreProcessor\Instruction\MagentoImport $magentoImportProcessor
     * @param \Magento\Framework\Less\PreProcessor\Instruction\Import $importProcessor
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Less\PreProcessor\Instruction\MagentoImport $magentoImportProcessor,
        \Magento\Framework\Less\PreProcessor\Instruction\Import $importProcessor
    ) {
        $this->tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->lessDirectory = Source::TMP_MATERIALIZATION_DIR . '/' . self::TMP_LESS_DIR;
        $this->assetRepo = $assetRepo;
        $this->magentoImportProcessor = $magentoImportProcessor;
        $this->importProcessor = $importProcessor;
    }

    /**
     * Create a tree of self-sustainable files and return the topmost LESS file, ready for passing to 3rd party library
     *
     * @param \Magento\Framework\View\Asset\PreProcessor\Chain $chain
     * @return string Absolute path of generated LESS file
     */
    public function generateLessFileTree(\Magento\Framework\View\Asset\PreProcessor\Chain $chain)
    {
        /**
         * @bug This logic is duplicated at \Magento\Framework\View\Asset\PreProcessor\Pool::getPreProcessors()
         * If you need to extend or modify behavior of LESS preprocessing, you must account for both places
         */

        /**
         * wait if generation process has already started
         */
        while ($this->isProcessLocked()) {
            sleep(1);
        }
        $lockFilePath = $this->lessDirectory . '/' . self::LOCK_FILE;
        $this->tmpDirectory->writeFile($lockFilePath, time());

        $this->magentoImportProcessor->process($chain);
        $this->importProcessor->process($chain);
        $this->generateRelatedFiles();
        $lessRelativePath = preg_replace('#\.css$#', '.less', $chain->getAsset()->getPath());
        $tmpFilePath = $this->createFile($lessRelativePath, $chain->getContent());
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
        $lockFilePath = $this->lessDirectory . '/' . self::LOCK_FILE;
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

    /**
     * Create all asset files, referenced from already processed ones
     *
     * @return void
     */
    protected function generateRelatedFiles()
    {
        do {
            $relatedFiles = $this->importProcessor->getRelatedFiles();
            $this->importProcessor->resetRelatedFiles();
            foreach ($relatedFiles as $relatedFileInfo) {
                list($relatedFileId, $asset) = $relatedFileInfo;
                $this->generateRelatedFile($relatedFileId, $asset);
            }
        } while ($relatedFiles);
    }

    /**
     * Create file, referenced relatively to an asset
     *
     * @param string $relatedFileId
     * @param LocalInterface $asset
     * @return void
     */
    protected function generateRelatedFile($relatedFileId, LocalInterface $asset)
    {
        $relatedAsset = $this->assetRepo->createRelated($relatedFileId, $asset);
        $this->createFile($relatedAsset->getPath(), $relatedAsset->getContent());
    }

    /**
     * Write down contents to a temporary file and return its absolute path
     *
     * @param string $relativePath
     * @param string $contents
     * @return string
     */
    protected function createFile($relativePath, $contents)
    {
        $filePath = $this->lessDirectory . '/' . $relativePath;

        if (!$this->tmpDirectory->isExist($filePath)) {
            $this->tmpDirectory->writeFile($filePath, $contents);
        }
        return $this->tmpDirectory->getAbsolutePath($filePath);
    }
}
