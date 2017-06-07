<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Controller\Adminhtml\Product\Gallery;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;
use \Magento\Framework\Validator\AllowedProtocols;
use \Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * URI validator
     *
     * @var AllowedProtocols
     */
    private $validator;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension
     */
    private $extensionValidator;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    private $fileDriver;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Image\AdapterFactory $imageAdapterFactory
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     * @param \Magento\MediaStorage\Model\ResourceModel\File\Storage\File $fileUtility
     * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $extensionValidator
     * @param \Magento\Framework\Filesystem\DriverInterface $fileDriver
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Image\AdapterFactory $imageAdapterFactory,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Magento\MediaStorage\Model\ResourceModel\File\Storage\File $fileUtility,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $extensionValidator = null,
        \Magento\Framework\Filesystem\DriverInterface $fileDriver = null
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->mediaConfig = $mediaConfig;
        $this->fileSystem = $fileSystem;
        $this->imageAdapter = $imageAdapterFactory->create();
        $this->curl = $curl;
        $this->fileUtility = $fileUtility;
        $this->extensionValidator = $extensionValidator
            ?: $this->_objectManager->get(\Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class);
        $this->fileDriver = $fileDriver
            ?: $this->_objectManager->get(\Magento\Framework\Filesystem\DriverInterface::class);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $baseTmpMediaPath = $this->mediaConfig->getBaseTmpMediaPath();
        try {
            $remoteFileUrl = $this->getRequest()->getParam('remote_image');
            $this->validateRemoteFile($remoteFileUrl);
            $originalFileName = basename($remoteFileUrl);
            $localFileName = Uploader::getCorrectFileName($originalFileName);
            $localTmpFileName = Uploader::getDispretionPath($localFileName) . DIRECTORY_SEPARATOR . $localFileName;
            $localFileMediaPath = $baseTmpMediaPath . ($localTmpFileName);
            $localUniqueFileMediaPath = $this->appendNewFileName($localFileMediaPath);
            $this->validateRemoteFileExtensions($localUniqueFileMediaPath);
            $this->retrieveRemoteImage($remoteFileUrl, $localUniqueFileMediaPath);
            $localFileFullPath = $this->appendAbsoluteFileSystemPath($localUniqueFileMediaPath);
            $this->imageAdapter->validateUploadFile($localFileFullPath);
            $result = $this->appendResultSaveRemoteImage($localUniqueFileMediaPath);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            if (isset($localFileFullPath) && $this->fileDriver->isExists($localFileFullPath)) {
                $this->fileDriver->deleteFile($localFileFullPath);
            }
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }

    /**
     * Get URI validator
     *
     * @return AllowedProtocols
     */
    private function getValidator()
    {
        if ($this->validator === null) {
            $this->validator = $this->_objectManager->get(AllowedProtocols::class);
        }

        return $this->validator;
    }

    /**
     * Validate remote file
     *
     * @param string $remoteFileUrl
     * @throws LocalizedException
     *
     * @return $this
     */
    private function validateRemoteFile($remoteFileUrl)
    {
        /** @var AllowedProtocols $validator */
        $validator = $this->getValidator();
        if (!$validator->isValid($remoteFileUrl)) {
            throw new LocalizedException(
                __("Protocol isn't allowed")
            );
        }

        return $this;
    }

    /**
     * Invalidates files that have script extensions.
     *
     * @param string $filePath
     * @throws \Magento\Framework\Exception\ValidatorException
     * @return void
     */
    private function validateRemoteFileExtensions($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!$this->extensionValidator->isValid($extension)) {
            throw new \Magento\Framework\Exception\ValidatorException(__('Disallowed file type.'));
        }
    }

    /**
     * @param string $fileName
     * @return mixed
     */
    protected function appendResultSaveRemoteImage($fileName)
    {
        $fileInfo = pathinfo($fileName);
        $tmpFileName = Uploader::getDispretionPath($fileInfo['basename']) . DIRECTORY_SEPARATOR . $fileInfo['basename'];
        $result['name'] = $fileInfo['basename'];
        $result['type'] = $this->imageAdapter->getMimeType();
        $result['error'] = 0;
        $result['size'] = filesize($this->appendAbsoluteFileSystemPath($fileName));
        $result['url'] = $this->mediaConfig->getTmpMediaUrl($tmpFileName);
        $result['file'] = $tmpFileName;
        return $result;
    }

    /**
     * @param string $fileUrl
     * @param string $localFilePath
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function retrieveRemoteImage($fileUrl, $localFilePath)
    {
        $this->curl->setConfig(['header' => false]);
        $this->curl->write('GET', $fileUrl);
        $image = $this->curl->read();
        if (empty($image)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not get preview image information. Please check your connection and try again.')
            );
        }
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
