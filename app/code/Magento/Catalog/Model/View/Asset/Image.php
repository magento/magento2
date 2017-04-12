<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\View\Asset;

use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\LocalInterface;

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
     * Image constructor.
     *
     * @param ConfigInterface $mediaConfig
     * @param ContextInterface $context
     * @param EncryptorInterface $encryptor
     * @param string $filePath
     * @param array $miscParams
     */
    public function __construct(
        ConfigInterface $mediaConfig,
        ContextInterface $context,
        EncryptorInterface $encryptor,
        $filePath,
        array $miscParams = []
    ) {
        $this->mediaConfig = $mediaConfig;
        $this->context = $context;
        $this->filePath = $filePath;
        $this->miscParams = $miscParams;
        $this->encryptor = $encryptor;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->context->getBaseUrl() . $this->getRelativePath(DIRECTORY_SEPARATOR);
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
        return $this->getAbsolutePath($this->context->getPath());
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
        return trim(
            $path . ($item ? DIRECTORY_SEPARATOR . ltrim($item, DIRECTORY_SEPARATOR) : ''),
            DIRECTORY_SEPARATOR
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFile()
    {
        return $this->mediaConfig->getBaseMediaPath()
            . DIRECTORY_SEPARATOR . ltrim($this->filePath, DIRECTORY_SEPARATOR);
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
     * Generate absolute path
     *
     * @param string $result
     * @return string
     */
    private function getAbsolutePath($result)
    {
        $prefix = (substr($result, 0, 1) == DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';
        $result = $this->join($result, $this->getModule());
        $result = $this->join($result, $this->getMiscPath());
        $result = $this->join($result, $this->getFilePath());
        return $prefix . $result;
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
        return DIRECTORY_SEPARATOR . $result;
    }
}
