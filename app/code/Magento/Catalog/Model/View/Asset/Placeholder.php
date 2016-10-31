<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\View\Asset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Repository;

/**
 * A locally available image placeholder file asset that can be referred with a file type
 */
class Placeholder implements LocalInterface
{
    /**
     * Type of placeholder
     *
     * @var string
     */
    private $type;

    /**
     * Filevpath of placeholder
     *
     * @var string
     */
    private $filePath;

    /**
     * @var string
     */
    private $contentType = 'image';

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Placeholder constructor.
     *
     * @param ContextInterface $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository $assetRepo
     * @param string $type
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
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
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
            $result = $defaultPlaceholder->getSourceFile();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFile()
    {
        return $this->getPath();
    }

    /**
     * Get source content type
     *
     * @return string
     */
    public function getSourceContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
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
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function getModule()
    {
        return 'placeholder';
    }
}
