<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\View\Asset;

use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\OsInfo;

/**
 * A locally available image file asset that can be referred with a file path
 *
 * This class is a value object with lazy loading of some of its data (content, physical file path)
 */
class Image implements LocalInterface
{
    /**
     * @var string
     */
    private $placeholder = 'Magento_Catalog::images/product/placeholder/%s.jpg';

    /**
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
     * Misc image params depend on size, transparency, quality, watermark etc.
     *
     * @var array
     */
    private $miscParams;

    /**
     * @var ConfigInterface
     */
    private $mediaConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var OsInfo
     */
    private $osInfo;

    /**
     * Image constructor.
     *
     * @param ConfigInterface $mediaConfig
     * @param ContextInterface $context
     * @param EncryptorInterface $encryptor
     * @param string $filePath
     * @param Repository $assetRepo
     * @param OsInfo $osInfo
     * @param array $miscParams
     */
    public function __construct(
        ConfigInterface $mediaConfig,
        ContextInterface $context,
        EncryptorInterface $encryptor,
        $filePath,
        Repository $assetRepo,
        OsInfo $osInfo,
        array $miscParams = []
    ) {
        $this->mediaConfig = $mediaConfig;
        $this->context = $context;
        $this->filePath = $filePath;
        $this->miscParams = $miscParams;
        $this->encryptor = $encryptor;
        $this->assetRepo = $assetRepo;
        $this->osInfo = $osInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        if (!$this->getFilePath()) {
            return $this->getDefaultPlaceHolderUrl();
        }

        return $this->context->getBaseUrl() . '/' . $this->getRelativePath('/');
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
        if (!$this->getFilePath()) {
            $asset = $this->assetRepo->createAsset($this->getPlaceHolder());
            return $asset->getSourceFile();
        }
        $relativePath = $this->getRelativePath($this->context->getPath());
        if ($this->osInfo->isWindows()) {
            return $relativePath;
        }
        return '/' . $relativePath;
    }

    /**
     * Subroutine for building path
     *
     * @param string $path
     * @param string $item
     * @return string
     */
    private function join($path, $item)
    {
        return trim($path . ($item ? '/' . ltrim($item, '/') : ''), '/');
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFile()
    {
        return $this->mediaConfig->getBaseMediaPath() . '/' . ltrim($this->filePath, '/');
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
        return 'cache';
    }

    /**
     * Retrieve part of path based on misc params
     *
     * @return string
     */
    private function getMiscPath()
    {
        return $this->encryptor->hash(implode('_', $this->miscParams), Encryptor::HASH_VERSION_MD5);
    }

    /**
     * Get placeholder for asset creation
     *
     * @return string
     */
    private function getPlaceHolder()
    {
        return sprintf($this->placeholder, $this->miscParams['image_type']);
    }

    /**
     * Return default placeholder URL
     *
     * @return string
     */
    private function getDefaultPlaceHolderUrl()
    {
        return $this->assetRepo->getUrl($this->getPlaceHolder());
    }

    /**
     * Generate relative path
     *
     * @param string $result
     * @return string
     */
    private function getRelativePath($result)
    {
        $result = $this->join($result, $this->getModule());
        $result = $this->join($result, $this->getMiscPath());
        $result = $this->join($result, $this->getFilePath());
        return $result;
    }
}
