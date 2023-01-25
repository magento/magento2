<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Model\Css\PreProcessor\FileGenerator;

use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Framework\Css\PreProcessor\FileGenerator\RelatedGenerator;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Repository;

/**
 * Decorator for publishing of related assets
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
     * @var State
     */
    private $state;

    /**
     * @var bool
     */
    private $hasRelatedPublishing;

    /**
     * @param Repository $assetRepository
     * @param Temporary $temporaryFile
     * @param Publisher $assetPublisher
     * @param ScopeConfigInterface $scopeConfig
     * @param State $state
     * @param bool $hasRelatedPublishing
     */
    public function __construct(
        Repository $assetRepository,
        Temporary $temporaryFile,
        Publisher $assetPublisher,
        ScopeConfigInterface $scopeConfig,
        State $state,
        $hasRelatedPublishing = false
    ) {
        parent::__construct($assetRepository, $temporaryFile);
        $this->assetPublisher = $assetPublisher;
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
        $this->hasRelatedPublishing = $hasRelatedPublishing;
    }

    /**
     * @inheritdoc
     */
    protected function generateRelatedFile($relatedFileId, LocalInterface $asset)
    {
        $relatedAsset = parent::generateRelatedFile($relatedFileId, $asset);
        $isClientSideCompilation =
            $this->state->getMode() !== State::MODE_PRODUCTION
            && WorkflowType::CLIENT_SIDE_COMPILATION === $this->scopeConfig->getValue(WorkflowType::CONFIG_NAME_PATH);

        if ($this->hasRelatedPublishing || $isClientSideCompilation) {
            $this->assetPublisher->publish($relatedAsset);
        }

        return $relatedAsset;
    }
}
