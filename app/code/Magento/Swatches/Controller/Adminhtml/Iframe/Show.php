<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Controller\Adminhtml\Iframe;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read as DirectoryRead;
use Magento\Framework\Image\Adapter\AdapterInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Swatches\Helper\Media as SwatchMediaHelper;

/**
 * Class to show swatch image and save it on disk
 */
class Show extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Swatches::iframe';

    /**
     * @param Context $context
     * @param SwatchMediaHelper $swatchHelper Helper to move image from tmp to catalog
     * @param AdapterFactory $adapterFactory
     * @param MediaConfig $config
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(
        Context $context,
        protected readonly SwatchMediaHelper $swatchHelper,
        protected readonly AdapterFactory $adapterFactory,
        protected readonly MediaConfig $config,
        protected readonly Filesystem $filesystem,
        protected readonly UploaderFactory $uploaderFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Image upload action in iframe
     *
     * @return void
     */
    public function execute()
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'datafile']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            /** @var AdapterInterface $imageAdapter */
            $imageAdapter = $this->adapterFactory->create();
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            /** @var DirectoryRead $mediaDirectory */
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $config = $this->config;
            $result = $uploader->save($mediaDirectory->getAbsolutePath($config->getBaseTmpMediaPath()));
            unset($result['path']);

            $this->_eventManager->dispatch(
                'swatch_gallery_upload_image_after',
                ['result' => $result, 'action' => $this]
            );

            unset($result['tmp_name']);

            $result['url'] = $this->config->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';

            $newFile = $this->swatchHelper->moveImageFromTmp($result['file']);
            $this->swatchHelper->generateSwatchVariations($newFile);
            $fileData = ['swatch_path' => $this->swatchHelper->getSwatchMediaUrl(), 'file_path' => $newFile];
            $this->getResponse()->setBody(json_encode($fileData));
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $this->getResponse()->setBody(json_encode($result));
        }
    }
}
