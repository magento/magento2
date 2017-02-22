<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\FileGenerator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Css\PreProcessor\Instruction\Import;
use Magento\Framework\View\Asset\LocalInterface;

/**
 * Class RelatedGenerator
 */
class RelatedGenerator
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $tmpDirectory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * @var \Magento\Framework\Css\PreProcessor\File\Temporary
     */
    private $temporaryFile;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     * @param \Magento\Framework\Css\PreProcessor\File\Temporary $temporaryFile
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\Css\PreProcessor\File\Temporary $temporaryFile
    ) {
        $this->tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->assetRepository = $assetRepository;
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
        $relatedAsset = $this->assetRepository->createRelated($relatedFileId, $asset);
        $this->temporaryFile->createFile($relatedAsset->getPath(), $relatedAsset->getContent());

        return $relatedAsset;
    }
}
