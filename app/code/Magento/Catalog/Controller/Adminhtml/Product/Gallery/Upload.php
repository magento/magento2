<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Gallery;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Image\UploadResizeConfigInterface;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\Uploader;

/**
 * Upload media gallery for Products
 */
class Upload extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

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
     * @var AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Config
     */
    private $productMediaConfig;

    /**
     * @var UploadResizeConfigInterface
     */
    private $imageUploadConfig;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param AdapterFactory $adapterFactory
     * @param Filesystem $filesystem
     * @param Config $productMediaConfig
     * @param UploadResizeConfigInterface|null $imageUploadConfig
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        AdapterFactory $adapterFactory = null,
        Filesystem $filesystem = null,
        Config $productMediaConfig = null,
        UploadResizeConfigInterface $imageUploadConfig = null
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->adapterFactory = $adapterFactory ?: ObjectManager::getInstance()
            ->get(AdapterFactory::class);
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()
            ->get(Filesystem::class);
        $this->productMediaConfig = $productMediaConfig ?: ObjectManager::getInstance()
            ->get(Config::class);
        $this->imageUploadConfig = $imageUploadConfig
            ?: ObjectManager::getInstance()->get(UploadResizeConfigInterface::class);
    }

    /**
     * Upload image(s) to the product gallery.
     *
     * @return Raw
     */
    public function execute()
    {
        try {
            /** @var Uploader $uploader */
            $uploader = $this->_objectManager->create(
                Uploader::class,
                ['fileId' => 'image']
            );
            $uploader->setAllowedExtensions($this->getAllowedExtensions());
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
            $imageAdapter = $this->adapterFactory->create();
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $result = $uploader->save(
                $mediaDirectory->getAbsolutePath($this->productMediaConfig->getBaseTmpMediaPath())
            );

            list($imageWidth, $imageHeight) = getimagesize($result["path"] . $result["file"]);
            if ($imageHeight > $this->getImageUploadMaxHeight() || $imageWidth > $this->getImageUploadMaxHeight()) {
                $imageAdapter->open($result["path"] . $result["file"]);
                $imageAdapter->keepAspectRatio(true);
                $imageAdapter->resize($this->getImageUploadMaxWidth(), $this->getImageUploadMaxHeight());
                $imageAdapter->save();
            }

            $this->_eventManager->dispatch(
                'catalog_product_gallery_upload_image_after',
                ['result' => $result, 'action' => $this]
            );

            unset($result['tmp_name']);
            unset($result['path']);

            $result['url'] = $this->productMediaConfig->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var Raw $response */
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
     * Get Image Upload Maximum Width Config.
     *
     * @return int
     */
    private function getImageUploadMaxWidth()
    {
        return $this->imageUploadConfig->getMaxWidth();
    }

    /**
     * Get Image Upload Maximum Height Config.
     *
     * @return int
     */
    private function getImageUploadMaxHeight()
    {
        return $this->imageUploadConfig->getMaxHeight();
    }
}
