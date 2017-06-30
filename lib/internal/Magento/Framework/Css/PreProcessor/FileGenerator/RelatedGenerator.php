<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\FileGenerator;

use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Framework\Css\PreProcessor\Instruction\Import;
use Magento\Framework\View\Asset\LocalInterface;

/**
 * Class RelatedGenerator
 */
class RelatedGenerator
{
    /**
     * @var Repository
     */
    private $assetRepository;

    /**
     * @var Temporary
     */
    private $temporaryFile;

    /**
     * @param Repository $assetRepository
     * @param Temporary $temporaryFile
     */
    public function __construct(
        Repository $assetRepository,
        Temporary $temporaryFile
    ) {
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
