<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Css\PreProcessor\FileGenerator;

use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\Css\PreProcessor\FileGenerator\RelatedGenerator;

/**
 * Class PublicationDecorator
 *
 * Decorates generator of related assets and publishes them
 */
class PublicationDecorator extends RelatedGenerator
{
    /**
     * @var \Magento\Framework\App\View\Asset\Publisher
     */
    private $assetPublisher;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Css\PreProcessor\File\Temporary $temporaryFile
     * @param \Magento\Framework\App\View\Asset\Publisher $assetPublisher
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Css\PreProcessor\File\Temporary $temporaryFile,
        \Magento\Framework\App\View\Asset\Publisher $assetPublisher
    ) {
        parent::__construct($filesystem, $assetRepo, $temporaryFile);
        $this->assetPublisher = $assetPublisher;
    }

    /**
     * @inheritdoc
     */
    protected function generateRelatedFile($relatedFileId, LocalInterface $asset)
    {
        $relatedAsset = parent::generateRelatedFile($relatedFileId, $asset);
        $this->assetPublisher->publish($relatedAsset);

        return $relatedAsset;
    }
}
