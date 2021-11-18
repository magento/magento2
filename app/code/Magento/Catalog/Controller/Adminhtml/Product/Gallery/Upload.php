<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Gallery;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Model\Image\UploadResizeConfigInterface;

/**
 * Class Upload
 */
class Upload extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var array
     */
    private $allowedMimeTypes = [
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/png',
        'png' => 'image/gif'
    ];

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $productMediaConfig;

    /**
     * @var \Magento\Backend\Model\Image\UploadResizeConfigInterface
     */
    private $imageUploadConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Catalog\Model\Product\Media\Config $productMediaConfig
     * @param UploadResizeConfigInterface $imageUploadConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $adapterFactory = null,
        \Magento\Catalog\Model\Product\Media\Config $productMediaConfig = null,
        UploadResizeConfigInterface $imageUploadConfig = null
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->adapterFactory = $adapterFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Image\AdapterFactory::class);
        $this->productMediaConfig = $productMediaConfig ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\Product\Media\Config::class);
        $this->imageUploadConfig = $imageUploadConfig ?: ObjectManager::getInstance()
            ->get(UploadResizeConfigInterface::class);
    }

    /**
     * Upload image(s) to the product gallery.
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions($this->getAllowedExtensions());
            $imageAdapter = $this->adapterFactory->create();
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                $this->mediaDirectory->getAbsolutePath($this->productMediaConfig->getBaseTmpMediaPath())
            );
            $this->resizeImage($imageAdapter, $result['path'], $result['file']);
            $this->_eventManager->dispatch(
                'catalog_product_gallery_upload_image_after',
                ['result' => $result, 'action' => $this]
            );

            unset($result['tmp_name']);
            unset($result['path']);

            $result['url'] = $this->productMediaConfig->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';
        } catch (LocalizedException $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        } catch (\Throwable $e) {
            $result = ['error' => 'Something went wrong while saving the file(s).', 'errorcode' => 0];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }

    /**
     * Get the set of allowed file extensions.
     *
     * @return array
     */
    private function getAllowedExtensions()
    {
        return array_keys($this->allowedMimeTypes);
    }

    /**
     * Resize the image
     *
     * @param \Magento\Framework\Image\AdapterFactory $imageAdapter
     * @param string $path
     * @param string $file
     * @return bool
     */
    private function resizeImage($imageAdapter, $path, $file)
    {
        try {
            # Get width and height of the uploaded image
            list($imageWidth, $imageHeight) = getimagesize($path . $file);
            if ($this->imageUploadConfig->isResizeEnabled()
                && $imageHeight > $this->imageUploadConfig->getMaxHeight()
                || $imageWidth > $this->imageUploadConfig->getMaxWidth()
            ) {
                $imageAdapter->open($path . $file);
                $imageAdapter->keepAspectRatio(true);
                $imageAdapter->resize($this->imageUploadConfig->getMaxWidth(), $this->imageUploadConfig->getMaxHeight());
                $imageAdapter->save();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
