<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\View\Asset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Catalog\Model\Product\Media\ConfigInterface;

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
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directoryMedia;

    /**
     * @var ConfigInterface
     */
    private $mediaConfig;

    /**
     * Placeholder constructor.
     *
     * @param ContextInterface $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository $assetRepo
     * @param string $type
     * @param Filesystem|null $filesystem
     * @param ConfigInterface|null $mediaConfig
     *
     */
    public function __construct(
        ContextInterface $context,
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepo,
        $type,
        ?Filesystem $filesystem = null,
        ?ConfigInterface $mediaConfig = null
    ) {
        $this->context = $context;
        $this->scopeConfig = $scopeConfig;
        $this->assetRepo = $assetRepo;
        $this->type = $type;
        $filesystem = $filesystem ?? ObjectManager::getInstance()->get(Filesystem::class);
        $this->mediaConfig = $mediaConfig ?? ObjectManager::getInstance()->get(ConfigInterface::class);
        $this->directoryMedia = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA,
            DriverPool::FILE
        );
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @inheritdoc
     */
    public function getPath()
    {
        if ($this->getFilePath() !== null) {
            $result = $this->getLocalMediaPath()
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
     * Get path for local media
     *
     * @return string
     */
    private function getLocalMediaPath()
    {
        return $this->directoryMedia->getAbsolutePath($this->mediaConfig->getBaseMediaPath());
    }

    /**
     * Get relative placeholder path
     *
     * @return string|null
     */
    public function getRelativePath()
    {
        $result = null;
        //will use system placeholder unless another specified in the config
        if ($this->getFilePath() !== null) {
            $result = DIRECTORY_SEPARATOR . DirectoryList::MEDIA
                . DIRECTORY_SEPARATOR . $this->directoryMedia->getRelativePath($this->mediaConfig->getBaseMediaPath())
                . DIRECTORY_SEPARATOR . $this->getModule()
                . DIRECTORY_SEPARATOR . $this->getFilePath();
        }
        return $result;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getContent()
    {
        return null;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @inheritdoc
     */
    public function getModule()
    {
        return 'placeholder';
    }
}
