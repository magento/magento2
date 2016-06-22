<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Css\PreProcessor\FileGenerator;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManager;
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
     * @var State
     */
    private $state;

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
        $isClientSideCompilation =
            $this->getAppMode() !== State::MODE_PRODUCTION
            && WorkflowType::CLIENT_SIDE_COMPILATION === $this->scopeConfig->getValue(WorkflowType::CONFIG_NAME_PATH);

        if ($this->hasRelatedPublishing || $isClientSideCompilation) {
            $this->assetPublisher->publish($relatedAsset);
        }

        return $relatedAsset;
    }

    /**
     * @return State
     * @deprecated
     */
    private function getState()
    {
        if (null === $this->state) {
            $this->state = ObjectManager::getInstance()->get(State::class);
        }

        return $this->state;
    }

    /**
     * TODO: Fix this in scope of MAGETWO-54595
     *
     * @return string
     * @deprecated
     */
    private function getAppMode()
    {
        return $this->getState() === State::MODE_DEFAULT
            ? ObjectManager::getInstance()->get(DeploymentConfig::class)->get(State::PARAM_MODE)
            : $this->getState()->getMode();
    }
}
