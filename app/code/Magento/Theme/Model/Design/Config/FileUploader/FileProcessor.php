<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\Design\Config\FileUploader;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Theme\Model\Design\Backend\File;
use Magento\Theme\Model\Design\BackendModelFactory;
use Magento\Theme\Model\Design\Config\MetadataProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileProcessor
{
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var BackendModelFactory
     */
    protected $backendModelFactory;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * Media Directory object (writable).
     *
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string
     */
    const FILE_DIR = 'design/file';

    /**
     * @param UploaderFactory $uploaderFactory
     * @param BackendModelFactory $backendModelFactory
     * @param MetadataProvider $metadataProvider
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        BackendModelFactory $backendModelFactory,
        MetadataProvider $metadataProvider,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->backendModelFactory = $backendModelFactory;
        $this->metadataProvider = $metadataProvider;
        $this->storeManager = $storeManager;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Save file to temp media directory
     *
     * @param  string $fileId
     * @return array
     * @throws LocalizedException
     */
    public function saveToTmp($fileId)
    {
        try {
            $result = $this->save($fileId, $this->getAbsoluteTmpMediaPath());
            $result['url'] = $this->getTmpMediaUrl($result['file']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $result;
    }

    /**
     * Retrieve temp media url
     *
     * @param string $file
     * @return string
     */
    protected function getTmpMediaUrl($file)
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . 'tmp/' . self::FILE_DIR . '/' . $this->prepareFile($file);
    }

    /**
     * Prepare file
     *
     * @param string $file
     * @return string
     */
    protected function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * Retrieve absolute temp media path
     *
     * @return string
     */
    protected function getAbsoluteTmpMediaPath()
    {
        return $this->mediaDirectory->getAbsolutePath('tmp/' . self::FILE_DIR);
    }

    /**
     * Save image
     *
     * @param string $fileId
     * @param string $destination
     * @return array
     * @throws LocalizedException
     */
    protected function save($fileId, $destination)
    {
        /** @var File $backendModel */
        $backendModel = $this->getBackendModel($fileId);
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $uploader->setAllowedExtensions($backendModel->getAllowedExtensions());
        $uploader->addValidateCallback('size', $backendModel, 'validateMaxSize');

        $result = $uploader->save($destination);
        unset($result['path']);

        return $result;
    }

    /**
     * Retrieve backend model by field code
     *
     * @param string $code
     * @return File
     * @throws LocalizedException
     */
    protected function getBackendModel($code)
    {
        $metadata = $this->metadataProvider->get();
        if (!(isset($metadata[$code]) && isset($metadata[$code]['backend_model']))) {
            throw new LocalizedException(__('The backend model isn\'t specified for "%1".', $code));
        }
        return $this->backendModelFactory->createByPath($metadata[$code]['path']);
    }
}
