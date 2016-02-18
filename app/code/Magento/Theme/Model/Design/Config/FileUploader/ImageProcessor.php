<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config\FileUploader;

use Magento\MediaStorage\Model\File\UploaderFactory;
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
     * @param  string $fileId
     * @return array
     * @throws \Exception
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
     * @param string $fileId
     * @param string $destination
     * @return array
     * @throws \Exception
     */
    protected function save($fileId, $destination)
    {
        $result = ['file' => '', 'size' => ''];
        /** @var \Magento\Theme\Model\Design\Backend\Image $backendModel */
        $backendModel = $this->getBackendModel($fileId);
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $uploader->setAllowedExtensions($backendModel->getAllowedExtensions());
        $uploader->addValidateCallback('size', $backendModel, 'validateMaxSize');
        return array_intersect_key($uploader->save($destination), $result);
    }

    /**
     * @param string $code
     * @return \Magento\Framework\App\Config\Value
     * @throws \Exception
     */
    protected function getBackendModel($code)
    {
        $metadata = $this->metadataProvider->get();
        if (!(isset($metadata[$code]) && isset($metadata[$code]['backend_model']))) {
            throw new \Exception('Backend model is not specified for ' . $code);
        }
        return $this->backendModelFactory->createByPath($metadata[$code]['path']);
    }
}
