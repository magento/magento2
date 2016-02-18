<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\FileUploader;

use Magento\MediaStorage\Model\File\UploaderFactory;

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
     * @param UploaderFactory $uploaderFactory
     * @param Config $imageConfig
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        Config $imageConfig
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->imageConfig = $imageConfig;
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
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        return array_intersect_key($uploader->save($destination), $result);
    }
}
