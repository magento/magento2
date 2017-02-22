<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Controller\Adminhtml\Iframe;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class to show swatch image and save it on disk
 */
class Show extends \Magento\Backend\App\Action
{
    /**
     * Helper to move image from tmp to catalog
     *
     * @var \Magento\Swatches\Helper\Media
     */
    protected $swatchHelper;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Swatches\Helper\Media $swatchHelper
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Catalog\Model\Product\Media\Config $config
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Swatches\Helper\Media $swatchHelper,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Catalog\Model\Product\Media\Config $config,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
    ) {
        $this->swatchHelper = $swatchHelper;
        $this->adapterFactory = $adapterFactory;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct($context);
    }

    /**
     * Image upload action in iframe
     *
     * @return string
     */
    public function execute()
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'datafile']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
            $imageAdapter = $this->adapterFactory->create();
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $config = $this->config;
            $result = $uploader->save($mediaDirectory->getAbsolutePath($config->getBaseTmpMediaPath()));

            $this->_eventManager->dispatch(
                'swatch_gallery_upload_image_after',
                ['result' => $result, 'action' => $this]
            );

            unset($result['tmp_name']);
            unset($result['path']);

            $result['url'] = $this->config->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';

            $newFile = $this->swatchHelper->moveImageFromTmp($result['file']);
            $this->swatchHelper->generateSwatchVariations($newFile);
            $fileData = ['swatch_path' => $this->swatchHelper->getSwatchMediaUrl(), 'file_path' => $newFile];
            $this->getResponse()->setBody(json_encode($fileData));
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $this->getResponse()->setBody(json_encode($result));
        }
    }

    /**
     * Check if user has enough privileges
     *
     * @codeCoverageIgnore
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Swatches::iframe');
    }
}
