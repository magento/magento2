<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\View\Asset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Repository;

/**
 * A locally available image placeholder file asset that can be referred with a file type
 * @since 2.2.0
 */
class Placeholder implements LocalInterface
{
    /**
     * Type of placeholder
     *
     * @var string
     * @since 2.2.0
     */
    private $type;

    /**
     * Filevpath of placeholder
     *
     * @var string
     * @since 2.2.0
     */
    private $filePath;

    /**
     * @var string
     * @since 2.2.0
     */
    private $contentType = 'image';

    /**
     * @var ContextInterface
     * @since 2.2.0
     */
    private $context;

    /**
     * @var Repository
     * @since 2.2.0
     */
    private $assetRepo;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * Placeholder constructor.
     *
     * @param ContextInterface $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository $assetRepo
     * @param string $type
     * @since 2.2.0
     */
    public function __construct(
        ContextInterface $context,
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepo,
        $type
    ) {
        $this->context = $context;
        $this->scopeConfig = $scopeConfig;
        $this->assetRepo = $assetRepo;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getUrl()
    {
        if ($this->getFilePath() !== null) {
            $result = $this->context->getBaseUrl() . '/' . $this->getModule() . '/' . $this->getFilePath();
        } else {
            $result = $this->assetRepo->getUrl("Magento_Catalog::images/product/placeholder/{$this->type}.jpg");
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getPath()
    {
        if ($this->getFilePath() !== null) {
            $result = $this->getContext()->getPath()
                . DIRECTORY_SEPARATOR . $this->getModule()
                . DIRECTORY_SEPARATOR . $this->getFilePath();
        } else {
            $defaultPlaceholder = $this->assetRepo->createAsset(
                "Magento_Catalog::images/product/placeholder/{$this->type}.jpg"
            );
            try {
                $result = $defaultPlaceholder->getSourceFile();
            } catch (NotFoundException $e) {
                $result = null;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getSourceFile()
    {
        return $this->getPath();
    }

    /**
     * Get source content type
     *
     * @return string
     * @since 2.2.0
     */
    public function getSourceContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getContent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getFilePath()
    {
        if ($this->filePath !== null) {
            return $this->filePath;
        }
        // check if placeholder defined in config
        $isConfigPlaceholder = $this->scopeConfig->getValue(
            "catalog/placeholder/{$this->type}_placeholder",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->filePath = $isConfigPlaceholder;

        return $this->filePath;
    }

    /**
     * {@inheritdoc}
     * @return ContextInterface
     * @since 2.2.0
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getModule()
    {
        return 'placeholder';
    }
}
