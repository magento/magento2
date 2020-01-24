<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableImportExport\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Uploader helper for downloadable products
 */
class Uploader extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Media files uploader
     *
     * @var \Magento\CatalogImportExport\Model\Import\Uploader
     */
    protected $fileUploader;

    /**
     * File helper downloadable product
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * Entity model parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public $connection;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Downloadable\Helper\File $fileHelper
     * @param \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Downloadable\Helper\File $fileHelper,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->fileHelper = $fileHelper;
        $this->fileUploader = $uploaderFactory->create();
        $this->fileUploader->init();
        $this->fileUploader->setAllowedExtensions($this->getAllowedExtensions());
        $this->fileUploader->removeValidateCallback('catalog_product_image');
        $this->connection = $resource->getConnection('write');
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
    }

    /**
     * Returns an object for upload a media files
     *
     * @param string $type
     * @param array $parameters
     * @return \Magento\CatalogImportExport\Model\Import\Uploader
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUploader($type, $parameters)
    {
        $dirConfig = DirectoryList::getDefaultConfig();
        $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];

        if (!empty($parameters[\Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR])) {
            $tmpPath = $parameters[\Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR];
        } else {
            $tmpPath = $dirAddon . '/' . $this->mediaDirectory->getRelativePath('import');
        }

        if (!$this->fileUploader->setTmpDir($tmpPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File directory \'%1\' is not readable.', $tmpPath)
            );
        }
        $destinationDir = "downloadable/files/" . $type;
        $destinationPath = $dirAddon . '/' . $this->mediaDirectory->getRelativePath($destinationDir);

        $this->mediaDirectory->create($destinationPath);
        if (!$this->fileUploader->setDestDir($destinationPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File directory \'%1\' is not writable.', $destinationPath)
            );
        }
        return $this->fileUploader;
    }

    /**
     * Check a file or directory exists
     *
     * @param string $fileName
     * @return bool
     */
    public function isFileExist(string $fileName): bool
    {
        return $this->mediaDirectory->isExist($this->fileUploader->getDestDir().$fileName);
    }

    /**
     * Get all allowed extensions
     *
     * @return array
     */
    protected function getAllowedExtensions()
    {
        $result = [];
        foreach (array_keys($this->fileHelper->getAllMineTypes()) as $option) {
            $result[] = substr($option, 1);
        }
        return $result;
    }
}
