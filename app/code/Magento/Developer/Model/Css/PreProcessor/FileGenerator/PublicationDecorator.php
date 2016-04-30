<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Css\PreProcessor\FileGenerator;

use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Framework\Css\PreProcessor\FileGenerator\RelatedGenerator;

/**
 * Class PublicationDecorator
 *
 * Decorates generator of related assets and publishes them
 */
class PublicationDecorator extends RelatedGenerator
{
    /**
     * @var Publisher
     */
    private $assetPublisher;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var bool
     */
    private $hasRelatedPublishing;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param Repository $assetRepository
     * @param Temporary $temporaryFile
     * @param Publisher $assetPublisher
     * @param ScopeConfigInterface $scopeConfig
     * @param bool $hasRelatedPublishing
     */
    public function __construct(
        Filesystem $filesystem,
        Repository $assetRepository,
        Temporary $temporaryFile,
        Publisher $assetPublisher,
        ScopeConfigInterface $scopeConfig,
        $hasRelatedPublishing = false
    ) {
        parent::__construct($filesystem, $assetRepository, $temporaryFile);
        $this->assetPublisher = $assetPublisher;
        $this->scopeConfig = $scopeConfig;
        $this->hasRelatedPublishing = $hasRelatedPublishing;
    }

    /**
     * @inheritdoc
     */
    protected function generateRelatedFile($relatedFileId, LocalInterface $asset)
    {
        $relatedAsset = parent::generateRelatedFile($relatedFileId, $asset);
        if ($this->hasRelatedPublishing
            || WorkflowType::CLIENT_SIDE_COMPILATION === $this->scopeConfig->getValue(WorkflowType::CONFIG_NAME_PATH)
        ) {
            $this->assetPublisher->publish($relatedAsset);
        }

        return $relatedAsset;
    }
}
