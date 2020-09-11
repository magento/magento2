<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaGalleryUi\Controller\Adminhtml\Image;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\File\WriteInterface;

/**
 * Class OnInsert
 */
class OnInsert extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var GetInsertImageContent
     */
    private $getInsertImageContent;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var WriteInterface
     */
    private $pubDirectory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param GetInsertImageContent|null $getInsertImageContent
     * @param Filesystem $fileSystem
     * @param Mime $mime
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        GetInsertImageContent $getInsertImageContent,
        Filesystem $fileSystem,
        Mime $mime
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->getInsertImageContent = $getInsertImageContent;
        $this->filesystem = $fileSystem;
        $this->mime = $mime;
    }

    /**
     * Return a content (just a link or an html block) for inserting image to the content
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $path = $this->getInsertImageContent->execute(
            $data['filename'],
            $data['force_static_path'],
            $data['as_is'],
            isset($data['store']) ? (int)$data['store'] : null
        );

        $size = $data['force_static_path'] ? $this->getImageSize($path) : 0;
        $type = $data['force_static_path'] ? $this->getMimeType($path) : '';
        return $this->resultJsonFactory->create()->setData(['path' => $path, 'size' => $size, 'type' => $type]);
    }

    /**
     * Retrieve size of requested file
     *
     * @param string $path
     * @return int
     */
    private function getImageSize(string $path): int
    {
        $directory = $this->getPubDirectory();

        return $directory->isExist($path) ? $directory->stat($path)['size'] : 0;
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $path
     * @return string
     */
    private function getMimeType(string $path)
    {
        $absoluteFilePath = $this->getPubDirectory()->getAbsolutePath($path);

        return $this->mime->getMimeType($absoluteFilePath);
    }

    /**
     * Retrieve pub directory read interface instance
     *
     * @return ReadInterface
     */
    private function getPubDirectory()
    {
        if ($this->pubDirectory === null) {
            $this->pubDirectory = $this->filesystem->getDirectoryRead(DirectoryList::PUB);
        }
        return $this->pubDirectory;
    }
}
