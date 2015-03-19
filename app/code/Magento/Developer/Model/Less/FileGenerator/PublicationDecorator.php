<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Less\FileGenerator;

use Magento\Framework\Less\FileGenerator\RelatedGenerator;
use Magento\Framework\View\Asset\LocalInterface;

/**
 * Class PublicationDecorator
 * Decorates generator of related assets and publishes them
 *
 * @package Magento\Developer\Model\Less\FileGenerator
 */
class PublicationDecorator extends RelatedGenerator
{
    /**
     * @var \Magento\Framework\App\View\Asset\Publisher
     */
    private $publisher;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Less\File\Temporary $temporaryFile
     * @param \Magento\Framework\App\View\Asset\Publisher $publisher
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Less\File\Temporary $temporaryFile,
        \Magento\Framework\App\View\Asset\Publisher $publisher
    ) {
        parent::__construct($filesystem, $assetRepo, $temporaryFile);
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
