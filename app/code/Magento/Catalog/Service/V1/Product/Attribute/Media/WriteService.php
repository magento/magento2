<?php
/**
 * Product Media Attribute
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Product\Attribute\Media;

use \Magento\Catalog\Service\V1\Product\Attribute\Media\Data\GalleryEntry;
use \Magento\Catalog\Service\V1\Product\Attribute\Media\Data\GalleryEntryContent;
use \Magento\Framework\App\Filesystem;
use \Magento\Catalog\Service\V1\Product\ProductLoader;
use \Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use \Magento\Catalog\Model\Product;
use \Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use \Magento\Catalog\Service\V1\Product\Attribute\Media\Data\GalleryEntryContentValidator;
use \Magento\Store\Model\StoreFactory;
use \Magento\Framework\Exception\InputException;
use \Magento\Framework\Exception\StateException;
use \Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WriteService implements WriteServiceInterface
{
    /**
     * MIME type/extension map
     *
     * @var array
     */
    private $mimeTypeExtensionMap = array(
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/png' => 'png',
    );

    /**
     * @var GalleryEntryContentValidator
     */
    private $contentValidator;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var GalleryEntryResolver
     */
    private $entryResolver;

    /**
     * @param GalleryEntryContentValidator $contentValidator
     * @param Filesystem $filesystem
     * @param ProductLoader $productLoader
     * @param MediaConfig $mediaConfig
     * @param StoreFactory $storeFactory
     * @param GalleryEntryResolver $entryResolver
     */
    public function __construct(
        GalleryEntryContentValidator $contentValidator,
        Filesystem $filesystem,
        ProductLoader $productLoader,
        MediaConfig $mediaConfig,
        StoreFactory $storeFactory,
        GalleryEntryResolver $entryResolver
    ) {
        $this->contentValidator = $contentValidator;
        $this->filesystem = $filesystem;
        $this->productLoader = $productLoader;
        $this->mediaConfig = $mediaConfig;
        $this->storeFactory = $storeFactory;
        $this->entryResolver = $entryResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function create($productSku, GalleryEntry $entry, GalleryEntryContent $entryContent, $storeId = 0)
    {
        $store = $this->storeFactory->create()->load($storeId);
        if ($store->getId() != $storeId) {
            throw new NoSuchEntityException('There is no store with provided ID.');
        }
        if (!$this->contentValidator->isValid($entryContent)) {
            throw new InputException('The image content is not valid.');
        }
        $product = $this->productLoader->load($productSku);

        $fileContent = @base64_decode($entryContent->getData(), true);
        $mediaTmpPath = $this->mediaConfig->getBaseTmpMediaPath();
        $mediaDirectory = $this->filesystem->getDirectoryWrite(Filesystem::MEDIA_DIR);
        $mediaDirectory->create($mediaTmpPath);
        $fileName = $entryContent->getName() . '.' . $this->mimeTypeExtensionMap[$entryContent->getMimeType()];
        $relativeFilePath = $mediaTmpPath . DIRECTORY_SEPARATOR . $fileName;
        $absoluteFilePath = $mediaDirectory->getAbsolutePath($relativeFilePath);
        $mediaDirectory->writeFile($relativeFilePath, $fileContent);

        /** @var $productMediaGallery \Magento\Catalog\Model\Product\Attribute\Backend\Media */
        $productMediaGallery = $this->getGalleryAttributeBackend($product);
        $imageFileUri = $productMediaGallery->addImage(
            $product,
            $absoluteFilePath,
            $entry->getTypes(),
            true,
            $entry->isDisabled()
        );
        // Update additional fields that are still empty after addImage call
        $productMediaGallery->updateImage($product, $imageFileUri, array(
            'label' => $entry->getLabel(),
            'position' => $entry->getPosition(),
            'disabled' => $entry->isDisabled(),
        ));
        $product->setStoreId($storeId);
        $product->save();
        // Remove all temporary files
        $mediaDirectory->delete($relativeFilePath);
        // File could change its name during the move from tmp dir
        return $this->entryResolver->getEntryIdByFilePath(
            $product,
            $productMediaGallery->getRenamedImage($imageFileUri)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function update($productSku, GalleryEntry $entry, $storeId = 0)
    {
        $store = $this->storeFactory->create()->load($storeId);
        if ($store->getId() != $storeId) {
            throw new NoSuchEntityException('There is no store with provided ID.');
        }
        $product = $this->productLoader->load($productSku);
        /** @var $productMediaGallery \Magento\Catalog\Model\Product\Attribute\Backend\Media */
        $productMediaGallery = $this->getGalleryAttributeBackend($product);
        $filePath = $this->entryResolver->getEntryFilePathById($product, $entry->getId());
        if (is_null($filePath)) {
            throw new NoSuchEntityException('There is no image with provided ID.');
        }

        $productMediaGallery->updateImage($product, $filePath, array(
            'label' => $entry->getLabel(),
            'position' => $entry->getPosition(),
            'disabled' => $entry->isDisabled(),
        ));
        $productMediaGallery->clearMediaAttribute($product, array_keys($product->getMediaAttributes()));
        $productMediaGallery->setMediaAttribute($product, $entry->getTypes(), $filePath);
        $product->setStoreId($storeId);
        $product->save();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($productSku, $entryId)
    {
        $product = $this->productLoader->load($productSku);
        /** @var $productMediaGallery \Magento\Catalog\Model\Product\Attribute\Backend\Media */
        $productMediaGallery = $this->getGalleryAttributeBackend($product);
        $filePath = $this->entryResolver->getEntryFilePathById($product, $entryId);
        if (is_null($filePath)) {
            throw new NoSuchEntityException('There is no image with provided ID.');
        }

        $productMediaGallery->removeImage($product, $filePath);
        $product->save();
        return true;
    }

    /**
     * Retrieve backend model of product media gallery attribute
     *
     * @param Product $product
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     * @throws StateException
     */
    protected function getGalleryAttributeBackend(Product $product)
    {
        $attributes = $product->getTypeInstance()->getSetAttributes($product);
        if (!isset($attributes['media_gallery']) || !($attributes['media_gallery'] instanceof AbstractAttribute)) {
            throw new StateException('Requested product does not support images.');
        }
        /** @var $galleryAttribute AbstractAttribute */
        $galleryAttribute = $attributes['media_gallery'];
        return $galleryAttribute->getBackend();
    }
}
