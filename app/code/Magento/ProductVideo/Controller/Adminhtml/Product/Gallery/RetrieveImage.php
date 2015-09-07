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
     * @var \Magento\MediaStorage\Model\Resource\File\Storage\File
     */
    protected $fileUtility;

    /**
     * @var \Magento\Framework\File\Mime
     */
    protected $mime;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileSizeService;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     * @param \Magento\MediaStorage\Model\Resource\File\Storage\File $fileUtility
     * @param \Magento\Framework\File\Mime $mime
     * @param \Magento\Framework\File\Size $fileSizeService
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Image\AdapterFactory $imageAdapterFactory,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Magento\MediaStorage\Model\Resource\File\Storage\File $fileUtility,
        \Magento\Framework\File\Mime $mime,
        \Magento\Framework\File\Size $fileSizeService
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->mediaConfig = $mediaConfig;
        $this->fileSystem = $fileSystem;
        $this->imageAdapter = $imageAdapterFactory->create();
        $this->curl = $curl;
        $this->fileUtility = $fileUtility;
        $this->mime = $mime;
        $this->fileSizeService = $fileSizeService;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        try {
//            $remoteFileUrl = $this->getRequest()->getParam('remote_image');
            $remoteFileUrl = "http://cs7051.vk.me/c540101/v540101915/2b173/MmvP_VEbG_0.jpg";
            $originalFileName = $this->parseOriginalFileName($remoteFileUrl);
            $localFileName = $this->localFileName($originalFileName);
            $localTmpFileName = $this->generateTmpFileName($localFileName);
            $localFileFullPath = $this->appendFileSystemPath($localTmpFileName);

            $this->retrieveRemoteImage($remoteFileUrl, $localTmpFileName);
            if (!$this->imageAdapter->validateUploadFile($localFileFullPath))
            {
                $result['error'] = 1;
            }
            else
            {
                $result['name'] = $localFileName;
                $result['type'] = $this->mime->getMimeType($localFileFullPath);
                $result['error'] = 0;
                $result['size'] = $this->fileSizeService->getMaxFileSize($localFileFullPath);
                $result['url'] = $this->mediaConfig->getTmpMediaUrl($localTmpFileName);
                $result['file'] = $this->generateFileNameWithPatch($localFileName);
            }
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }

    protected function localFileName($fileName)
    {
        $fileName = Uploader::getCorrectFileName($fileName);

        return strtolower($fileName);
    }
    /**
     * @param string $fileUrl
     * @param string $localFilePath
     * @return bool|void
     */
    protected function retrieveRemoteImage($fileUrl, $localFilePath)
    {
        $this->curl->write('GET', $fileUrl);
        $image = $this->curl->read();
        $this->fileUtility->saveFile($localFilePath, $image);
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
        return Uploader::getDispretionPath($fileName) . DIRECTORY_SEPARATOR . $fileName . '.tmp';
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function generateFileNameWithPatch($fileName)
    {
        return Uploader::getDispretionPath($fileName) . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param string $localTmpFile
     * @return string
     */
    protected function appendFileSystemPath($localTmpFile)
    {
        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $pathToSave = $mediaDirectory->getAbsolutePath($this->mediaConfig->getBaseTmpMediaPath());

        return $pathToSave . $localTmpFile;
    }
}
