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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $fileSystem
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Image\AdapterFactory $imageAdapterFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->mediaConfig = $mediaConfig;
        $this->fileSystem = $fileSystem;
        $this->imageAdapter = $imageAdapterFactory->create();
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        try {
            $remoteFileUrl = $this->getRequest()->getParam('remote_image');

            $localFileName = $this->parseOriginalFileName($remoteFileUrl);
            $localTmpFileName = $this->generateTmpFileName($localFileName);
            $localFileFullPath = $this->appendFileSystemPath($localTmpFileName);

            $this->retrieveRemoteImage($remoteFileUrl, $localFileFullPath);

            $result['url'] = $this->mediaConfig->getTmpMediaUrl($localTmpFileName);
            $result['file'] = $localFileName;
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
     * @param string $fileUrl
     * @param string $localFilePath
     * @return void
     */
    protected function retrieveRemoteImage($fileUrl, $localFilePath)
    {

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
