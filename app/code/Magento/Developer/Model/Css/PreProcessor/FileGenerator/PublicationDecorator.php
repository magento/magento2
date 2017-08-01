<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Css\PreProcessor\FileGenerator;

use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Framework\Css\PreProcessor\FileGenerator\RelatedGenerator;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Repository;

/**
 * Decorator for publishing of related assets
 * @since 2.0.0
 */
class PublicationDecorator extends RelatedGenerator
{
    /**
     * @var Publisher
     * @since 2.0.0
     */
    private $assetPublisher;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    private $scopeConfig;

    /**
     * @var bool
     * @since 2.0.0
     */
    private $hasRelatedPublishing;

    /**
     * @var State
     * @since 2.2.0
     */
    private $state;

    /**
     * Constructor
     *
     * @param Repository $assetRepository
     * @param Temporary $temporaryFile
     * @param Publisher $assetPublisher
     * @param ScopeConfigInterface $scopeConfig
     * @param bool $hasRelatedPublishing
     * @since 2.0.0
     */
    public function __construct(
        Repository $assetRepository,
        Temporary $temporaryFile,
        Publisher $assetPublisher,
        ScopeConfigInterface $scopeConfig,
        $hasRelatedPublishing = false
    ) {
        parent::__construct($assetRepository, $temporaryFile);
        $this->assetPublisher = $assetPublisher;
        $this->scopeConfig = $scopeConfig;
        $this->hasRelatedPublishing = $hasRelatedPublishing;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function generateRelatedFile($relatedFileId, LocalInterface $asset)
    {
        $relatedAsset = parent::generateRelatedFile($relatedFileId, $asset);
        $isClientSideCompilation =
            $this->getState()->getMode() !== State::MODE_PRODUCTION
            && WorkflowType::CLIENT_SIDE_COMPILATION === $this->scopeConfig->getValue(WorkflowType::CONFIG_NAME_PATH);

        if ($this->hasRelatedPublishing || $isClientSideCompilation) {
            $this->assetPublisher->publish($relatedAsset);
        }

        return $relatedAsset;
    }

    /**
     * @return State
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getState()
    {
        if (null === $this->state) {
            $this->state = ObjectManager::getInstance()->get(State::class);
        }

        return $this->state;
    }
}
