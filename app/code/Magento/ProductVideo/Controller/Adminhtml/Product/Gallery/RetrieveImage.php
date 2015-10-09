<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Controller\Adminhtml\Product\Gallery;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;

class RetrieveImage extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Framework\Image\Adapter\AbstractAdapter
     */
    protected $imageAdapter;

    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    protected $curl;

    /**
     * @var \Magento\MediaStorage\Model\ResourceModel\File\Storage\File
     */
    protected $fileUtility;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Image\AdapterFactory $imageAdapterFactory
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     * @param \Magento\MediaStorage\Model\ResourceModel\File\Storage\File $fileUtility
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Image\AdapterFactory $imageAdapterFactory,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Magento\MediaStorage\Model\ResourceModel\File\Storage\File $fileUtility
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->mediaConfig = $mediaConfig;
        $this->fileSystem = $fileSystem;
        $this->imageAdapter = $imageAdapterFactory->create();
        $this->curl = $curl;
        $this->fileUtility = $fileUtility;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        try {
            $remoteFileUrl = $this->getRequest()->getParam('remote_image');
            $originalFileName = $this->parseOriginalFileName($remoteFileUrl);
            $localFileName = $this->localFileName($originalFileName);
            $localTmpFileName = $this->generateTmpFileName($localFileName);
            $localFileMediaPath = $this->appendFileSystemPath($localTmpFileName);
            $localUniqueFileMediaPath = $this->appendNewFileName($localFileMediaPath);
            $this->retrieveRemoteImage($remoteFileUrl, $localUniqueFileMediaPath);
            $localFileFullPath = $this->appendAbsoluteFileSystemPath($localUniqueFileMediaPath);
            $this->imageAdapter->validateUploadFile($localFileFullPath);
            $result = $this->appendResultSaveRemoteImage($localUniqueFileMediaPath);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }

    /**
     * @param string $fileName
     * @return mixed
     */
    protected function appendResultSaveRemoteImage($fileName)
    {
        $fileInfo = pathinfo($fileName);
        $tmpFileName = $this->generateTmpFileName($fileInfo['basename']);
        $result['name'] = $fileInfo['basename'];
        $result['type'] = $this->imageAdapter->getMimeType();
        $result['error'] = 0;
        $result['size'] = filesize($this->appendAbsoluteFileSystemPath($fileName));
        $result['url'] = $this->mediaConfig->getTmpMediaUrl($tmpFileName);
        $result['file'] = $tmpFileName;
        return $result;
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function localFileName($fileName)
    {
        $fileName = Uploader::getCorrectFileName($fileName);
        return $fileName;
    }

    /**
     * @param string $fileUrl
     * @param string $localFilePath
     * @return bool|void
     */
    protected function retrieveRemoteImage($fileUrl, $localFilePath)
    {
        $this->curl->setConfig(['header' => false]);
        $this->curl->write('GET', $fileUrl);
        $image = $this->curl->read();
        $this->fileUtility->saveFile($localFilePath, $image);
    }

    /**
     * @param string $localFilePath
     * @return string
     */
    protected function appendNewFileName($localFilePath)
    {
        $destinationFile = $this->appendAbsoluteFileSystemPath($localFilePath);
        $fileName = Uploader::getNewFileName($destinationFile);
        $fileInfo = pathinfo($localFilePath);
        return $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param string $fileUrl
     * @return string
     */
    protected function parseOriginalFileName($fileUrl)
    {
        return basename($fileUrl);
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function generateTmpFileName($fileName)
    {
        return Uploader::getDispretionPath($fileName) . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function generateFileNameWithPath($fileName)
    {
        return Uploader::getDispretionPath($fileName) . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param string $localTmpFile
     * @return string
     */
    protected function appendFileSystemPath($localTmpFile)
    {
        $pathToSave = $this->mediaConfig->getBaseTmpMediaPath();
        return $pathToSave . $localTmpFile;
    }

    /**
     * @param string $localTmpFile
     * @return string
     */
    protected function appendAbsoluteFileSystemPath($localTmpFile)
    {
        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $pathToSave = $mediaDirectory->getAbsolutePath();
        return $pathToSave . $localTmpFile;
    }
}
