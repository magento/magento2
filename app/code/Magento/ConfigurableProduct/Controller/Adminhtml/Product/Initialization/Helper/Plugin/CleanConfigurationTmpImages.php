<?php
/**
 * Product initialization helper
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class CleanConfigurationTmpImages
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CleanConfigurationTmpImages
{
    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $fileStorageDb;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->request = $request;
        $this->fileStorageDb = $fileStorageDb;
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Clean Tmp configurable images
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $configurableProduct
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $configurableProduct
    ) {

        $configurations = $this->getConfigurations();
        foreach ($configurations as $variationId => $simpleProductData) {
            if (!isset($simpleProductData['media_gallery']['images'])) {
                continue;
            }

            foreach ($simpleProductData['media_gallery']['images'] as $imageId => $image) {
                $file = $this->getFilenameFromTmp($image['file']);
                if ($this->fileStorageDb->checkDbUsage()) {
                    $filename = $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getTmpMediaShortUrl($file));
                    $this->fileStorageDb->deleteFile($filename);
                } else {
                    $filename = $this->mediaConfig->getTmpMediaPath($file);
                    $this->mediaDirectory->delete($filename);
                }
            }
        }

        return $configurableProduct;
    }

    /**
     * Trim .tmp ending from filename
     *
     * @param string $file
     * @return string
     */
    protected function getFilenameFromTmp($file)
    {
        return strrpos($file, '.tmp') == strlen($file) - 4 ? substr($file, 0, strlen($file) - 4) : $file;
    }

    /**
     * Get configurations from request
     *
     * @return array
     */
    protected function getConfigurations()
    {
        $result = [];
        $configurableMatrix = $this->request->getParam('configurable-matrix-serialized', "[]");
        if (isset($configurableMatrix) && $configurableMatrix != "") {
            $configurableMatrix = json_decode($configurableMatrix, true);

            foreach ($configurableMatrix as $item) {
                if (empty($item['was_changed']) && empty($item['newProduct'])) {
                    continue;
                }

                $result[] = $item;
            }
        }

        return $result;
    }
}
