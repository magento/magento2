<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\FileGenerator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Css\PreProcessor\Instruction\Import;
use Magento\Framework\View\Asset\LocalInterface;

class RelatedGenerator
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $tmpDirectory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\Css\PreProcessor\File\Temporary
     */
    private $temporaryFile;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Css\PreProcessor\File\Temporary $temporaryFile
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Css\PreProcessor\File\Temporary $temporaryFile
    ) {
        $this->tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->assetRepo = $assetRepo;

        $this->temporaryFile = $temporaryFile;
    }

    /**
     * Create all asset files, referenced from already processed ones
     *
     * @param Import $importGenerator
     *
     * @return void
     */
    public function generate(Import $importGenerator)
    {
        do {
            $relatedFiles = $importGenerator->getRelatedFiles();
            $importGenerator->resetRelatedFiles();
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
     * @return \Magento\Framework\View\Asset\File
     */
    protected function generateRelatedFile($relatedFileId, LocalInterface $asset)
    {
        $relatedAsset = $this->assetRepo->createRelated($relatedFileId, $asset);
        $this->temporaryFile->createFile($relatedAsset->getPath(), $relatedAsset->getContent());

        return $relatedAsset;
    }
}
