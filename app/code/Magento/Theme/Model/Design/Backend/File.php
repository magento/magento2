<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Theme\Model\Design\Backend;

use Magento\Config\Model\Config\Backend\File as BackendFile;
use Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFileSystem;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Theme\Model\Design\Config\FileUploader\FileProcessor;

/**
 * File Backend
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class File extends BackendFile
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var IoFileSystem
     */
    private $ioFileSystem;

    /**
     * @var Database
     */
    private $databaseHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param UploaderFactory $uploaderFactory
     * @param RequestDataInterface $requestData
     * @param Filesystem $filesystem
     * @param UrlInterface $urlBuilder
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param Database $databaseHelper
     * @param IoFileSystem $ioFileSystem
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        UploaderFactory $uploaderFactory,
        RequestDataInterface $requestData,
        Filesystem $filesystem,
        UrlInterface $urlBuilder,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        Database $databaseHelper = null,
        IoFileSystem $ioFileSystem = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $resource,
            $resourceCollection,
            $data
        );
        $this->urlBuilder = $urlBuilder;
        $this->databaseHelper = $databaseHelper ?: ObjectManager::getInstance()->get(Database::class);
        $this->ioFileSystem = $ioFileSystem ?: ObjectManager::getInstance()->get(IoFileSystem::class);
    }

    /**
     * Save uploaded file and remote temporary file before saving config value
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $values = $this->getValue();
        $value = reset($values) ?: [];

        // Need to check name when it is uploaded in the media gallary
        $file = $value['file'] ?? $value['name'] ?? null;
        if (!isset($file)) {
            throw new LocalizedException(
                __('%1 does not contain field \'file\'', $this->getData('field_config/field'))
            );
        }

        if (!empty($this->getAllowedExtensions()) &&
            (!isset($this->ioFileSystem->getPathInfo($file)['extension']) ||
            !in_array($this->ioFileSystem->getPathInfo($file)['extension'], $this->getAllowedExtensions()))
        ) {
            throw new LocalizedException(
                __('Something is wrong with the file upload settings.')
            );
        }

        if (isset($value['exists'])) {
            $this->setValue($file);
            return $this;
        }

        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $this->updateMediaDirectory(basename($file), $value['url']);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function afterLoad()
    {
        $value = $this->getValue();
        if ($value && !is_array($value)) {
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            $fileName = $this->_getUploadDir() . '/' . basename($value);
            $fileInfo = null;
            if ($this->_mediaDirectory->isExist($fileName)) {
                $stat = $this->_mediaDirectory->stat($fileName);
                $url = $this->getStoreMediaUrl($value);
                $fileInfo = [
                    [
                        'url' => $url,
                        'file' => $value,
                        'size' => is_array($stat) ? $stat['size'] : 0,
                        //phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'name' => basename($value),
                        'type' => $this->getMimeType($fileName),
                        'exists' => true,
                    ]
                ];
            }
            $this->setValue($fileInfo);
        }
        return $this;
    }

    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return array
     */
    public function getAllowedExtensions()
    {
        return [];
    }

    /**
     * Retrieve upload directory path
     *
     * @param string $uploadDir
     * @return string
     */
    protected function getUploadDirPath($uploadDir)
    {
        return $this->_mediaDirectory->getRelativePath($uploadDir);
    }

    /**
     * Get Value
     *
     * @return array
     */
    public function getValue()
    {
        return $this->getData('value') ?: [];
    }

    /**
     * Retrieve store media url
     *
     * @param string $fileName
     * @return mixed
     */
    protected function getStoreMediaUrl($fileName)
    {
        $fieldConfig = $this->getFieldConfig();
        $baseUrl = '';
        $urlType = ['_type' => UrlInterface::URL_TYPE_MEDIA];
        if (isset($fieldConfig['base_url'])) {
            $baseUrl = $fieldConfig['base_url'];
            $urlType = ['_type' => empty($baseUrl['type']) ? 'link' : (string)$baseUrl['type']];
            $baseUrl = $baseUrl['value'] . '/';
        }
        return $this->urlBuilder->getBaseUrl($urlType) . $baseUrl . $fileName;
    }

    /**
     * Retrieve temp media path
     *
     * @param string $filename
     * @return string
     */
    protected function getTmpMediaPath($filename)
    {
        return 'tmp/' . FileProcessor::FILE_DIR . '/' . $filename;
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $fileName
     * @return string
     */
    private function getMimeType($fileName)
    {
        $absoluteFilePath = $this->_mediaDirectory->getAbsolutePath($fileName);

        $result = $this->getMime()->getMimeType($absoluteFilePath);
        return $result;
    }

    /**
     * Get Mime instance
     *
     * @return Mime
     *
     * @deprecated 100.2.0
     */
    private function getMime()
    {
        if ($this->mime === null) {
            $this->mime = ObjectManager::getInstance()->get(Mime::class);
        }
        return $this->mime;
    }

    /**
     * Get Relative Media Path
     *
     * @param string $path
     * @return string
     */
    private function getRelativeMediaPath(string $path): string
    {
        return preg_split('/\/(pub\/)?media\//', $path)[1] ?? preg_replace('/\/(pub\/)?media\//', '', $path);
    }

    /**
     * Move file to the correct media directory
     *
     * @param string $filename
     * @param string $url
     * @throws LocalizedException
     */
    private function updateMediaDirectory(string $filename, string $url)
    {
        $relativeMediaPath = $this->getRelativeMediaPath($url);
        $tmpMediaPath = $this->getTmpMediaPath($filename);
        $mediaPath = $this->_mediaDirectory->isFile($relativeMediaPath) ? $relativeMediaPath : $tmpMediaPath;
        $destinationMediaPath = $this->_getUploadDir() . '/' . $filename;

        $result = $mediaPath === $destinationMediaPath;
        if (!$result) {
            $result = $this->_mediaDirectory->copyFile(
                $mediaPath,
                $destinationMediaPath
            );
            $this->databaseHelper->renameFile(
                $mediaPath,
                $destinationMediaPath
            );
        }
        if ($result) {
            if ($mediaPath === $tmpMediaPath) {
                $this->_mediaDirectory->delete($mediaPath);
            }
            if ($this->_addWhetherScopeInfo()) {
                $filename = $this->_prependScopeInfo($filename);
            }
            $this->setValue($filename);
        } else {
            $this->unsValue();
        }
    }
}
