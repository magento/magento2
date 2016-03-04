<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config\FileUploader;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Theme\Model\Design\Backend\Image;
use Magento\Theme\Model\Design\BackendModelFactory;
use Magento\Theme\Model\Design\Config\MetadataProvider;

class ImageProcessor
{
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Config
     */
    protected $imageConfig;

    /**
     * @var BackendModelFactory
     */
    protected $backendModelFactory;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @param UploaderFactory $uploaderFactory
     * @param Config $imageConfig
     * @param BackendModelFactory $backendModelFactory
     * @param MetadataProvider $metadataProvider
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        Config $imageConfig,
        BackendModelFactory $backendModelFactory,
        MetadataProvider $metadataProvider
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->imageConfig = $imageConfig;
        $this->backendModelFactory = $backendModelFactory;
        $this->metadataProvider = $metadataProvider;
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
            $result = $this->save($fileId, $this->imageConfig->getAbsoluteTmpMediaPath());
            $result['url'] = $this->imageConfig->getTmpMediaUrl($result['file']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $result;
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
        $result = ['file' => '', 'size' => ''];
        /** @var Image $backendModel */
        $backendModel = $this->getBackendModel($fileId);
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $uploader->setAllowedExtensions($backendModel->getAllowedExtensions());
        $uploader->addValidateCallback('size', $backendModel, 'validateMaxSize');
        return array_intersect_key($uploader->save($destination), $result);
    }

    /**
     * Retrieve backend model by field code
     *
     * @param string $code
     * @return Image
     * @throws LocalizedException
     */
    protected function getBackendModel($code)
    {
        $metadata = $this->metadataProvider->get();
        if (!(isset($metadata[$code]) && isset($metadata[$code]['backend_model']))) {
            throw new LocalizedException(__('Backend model is not specified for %1', $code));
        }
        return $this->backendModelFactory->createByPath($metadata[$code]['path']);
    }
}
