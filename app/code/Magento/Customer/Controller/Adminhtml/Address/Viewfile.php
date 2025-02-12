<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Address;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Controller\Result\Raw;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;

/**
 * Class Viewfile serves to show file or image by file/image name provided in request parameters.
 */
class Viewfile extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var DecoderInterface
     */
    private $urlDecoder;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var IoFile
     */
    private $ioFile;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param RawFactory $resultRawFactory
     * @param DecoderInterface $urlDecoder
     * @param Filesystem $filesystem
     * @param Storage $storage
     * @param IoFile $ioFile
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        RawFactory $resultRawFactory,
        DecoderInterface $urlDecoder,
        Filesystem $filesystem,
        Storage $storage,
        IoFile $ioFile
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->urlDecoder  = $urlDecoder;
        $this->filesystem = $filesystem;
        $this->storage = $storage;
        $this->fileFactory = $fileFactory;
        $this->ioFile = $ioFile;
    }

    /**
     * Customer address view file action
     *
     * @return ResultInterface|ResponseInterface|void
     * @throws NotFoundException
     */
    public function execute()
    {
        list($file, $plain) = $this->getFileParams();

        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = AddressMetadataInterface::ENTITY_TYPE_ADDRESS . DIRECTORY_SEPARATOR .
            ltrim($file, DIRECTORY_SEPARATOR);
        $path = $directory->getAbsolutePath($fileName);
        if (mb_strpos($path, '..') !== false
            || (!$directory->isFile($fileName) && !$this->storage->processStorageFile($path))
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        $pathInfo = $this->ioFile->getPathInfo($path);
        if ($plain) {
            $extension = $pathInfo['extension'];
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }
            $stat = $directory->stat($fileName);
            $contentLength = $stat['size'];
            $contentModify = $stat['mtime'];

            /** @var Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify));
            $resultRaw->setContents($directory->readFile($fileName));

            return $resultRaw;
        } else {
            $name = $pathInfo['basename'];
            return $this->fileFactory->create(
                $name,
                ['type' => 'filename', 'value' => $fileName],
                DirectoryList::MEDIA
            );
        }
    }

    /**
     * Get parameters from request.
     *
     * @return array
     * @throws NotFoundException
     */
    private function getFileParams() : array
    {
        $plain = false;
        if ($this->getRequest()->getParam('file', '')) {
            // download file
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('file', '')
            );
        } elseif ($this->getRequest()->getParam('image', '')) {
            // show plain image
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('image', '')
            );
            $plain = true;
        } else {
            throw new NotFoundException(__('Page not found.'));
        }

        return [$file, $plain];
    }
}
