<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Less\FileGenerator;

use Magento\Framework\Less\FileGenerator\RelatedGenerator;
use Magento\Framework\View\Asset\LocalInterface;

class PublicationDecorator extends RelatedGenerator
{
    /**
     * @var \Magento\Framework\App\View\Asset\Publisher
     */
    private $publisher;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Less\PreProcessor\Instruction\Import $importProcessor,
        \Magento\Framework\Less\File\Temporary $temporaryFile,
        \Magento\Framework\App\View\Asset\Publisher $publisher
    ) {
        parent::__construct($filesystem, $assetRepo, $importProcessor, $temporaryFile);
        $this->publisher = $publisher;
    }

    /**
     * {inheritdoc}
     */
    protected function generateRelatedFile($relatedFileId, LocalInterface $asset)
    {
        $relatedAsset = parent::generateRelatedFile($relatedFileId, $asset);
        $this->publisher->publish($relatedAsset);
        return $relatedAsset;
    }
}
