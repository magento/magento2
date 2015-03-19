<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GalleryManagement implements \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface
{
    /**
     * MIME type/extension map
     *
     * @var array
     */
    protected $mimeTypeExtensionMap = [
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/png' => 'png',
    ];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var MediaConfig
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\ContentValidator
     */
    protected $contentValidator;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory
     */
    protected $entryFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media
     */
    protected $mediaGallery;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param MediaConfig $mediaConfig
     * @param ContentValidator $contentValidator
     * @param \Magento\Framework\Filesystem $filesystem
     * @param EntryResolver $entryResolver
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory $entryFactory
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $mediaGallery
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        MediaConfig $mediaConfig,
        \Magento\Catalog\Model\Product\Gallery\ContentValidator $contentValidator,
        \Magento\Framework\Filesystem $filesystem,
        EntryResolver $entryResolver,
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory $entryFactory,
        \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $mediaGallery,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->mediaConfig = $mediaConfig;
        $this->contentValidator = $contentValidator;
        $this->filesystem = $filesystem;
        $this->entryResolver = $entryResolver;
        $this->entryFactory = $entryFactory;
        $this->mediaGallery = $mediaGallery;
        $this->dataObjectHelper = $dataObjectHelper;
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
        if (!isset($attributes['media_gallery'])
            || !($attributes['media_gallery'] instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute)
        ) {
            throw new StateException(__('Requested product does not support images.'));
        }
        /** @var $galleryAttribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
        $galleryAttribute = $attributes['media_gallery'];
        return $galleryAttribute->getBackend();
    }

    /**
     * Retrieve assoc array that contains media attribute values of the given product
     *
     * @param Product $product
     * @return array
     */
    protected function getMediaAttributeValues(Product $product)
    {
        $mediaAttributeCodes = array_keys($product->getMediaAttributes());
        $mediaAttributeValues = [];
        foreach ($mediaAttributeCodes as $attributeCode) {
            $mediaAttributeValues[$attributeCode] = $product->getData($attributeCode);
        }
        return $mediaAttributeValues;
    }

    /**
     * {@inheritdoc}
     */
    public function create($product)
    {
        try {
            $this->storeManager->getStore($product->getStoreId());
        } catch (\Exception $exception) {
            throw new NoSuchEntityException(__('There is no store with provided ID.'));
        }
        /** @var $entry ProductAttributeMediaGalleryEntryInterface */
        $entry = $product->getCustomAttribute('media_gallery')->getValue();
        $entryContent = $entry->getContent();

        if (!$this->contentValidator->isValid($entryContent)) {
            throw new InputException(__('The image content is not valid.'));
        }
        $product = $this->productRepository->get($product->getSku());

        $fileContent = @base64_decode($entryContent->getEntryData(), true);
        $mediaTmpPath = $this->mediaConfig->getBaseTmpMediaPath();
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
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
        $productMediaGallery->updateImage(
            $product,
            $imageFileUri,
            [
                'label' => $entry->getLabel(),
                'position' => $entry->getPosition(),
                'disabled' => $entry->isDisabled(),
            ]
        );

        try {
            $this->productRepository->save($product);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot save product.'));
        }
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
    public function update($sku, ProductAttributeMediaGalleryEntryInterface $entry, $storeId = 0)
    {
        try {
            $this->storeManager->getStore($storeId);
        } catch (\Exception $exception) {
            throw new NoSuchEntityException(__('There is no store with provided ID.'));
        }
        $product = $this->productRepository->get($sku);
        /** @var $productMediaGallery \Magento\Catalog\Model\Product\Attribute\Backend\Media */
        $productMediaGallery = $this->getGalleryAttributeBackend($product);
        $filePath = $this->entryResolver->getEntryFilePathById($product, $entry->getId());
        if ($filePath === null) {
            throw new NoSuchEntityException(__('There is no image with provided ID.'));
        }

        $productMediaGallery->updateImage(
            $product,
            $filePath,
            [
                'label' => $entry->getLabel(),
                'position' => $entry->getPosition(),
                'disabled' => $entry->isDisabled(),
            ]
        );
        $productMediaGallery->clearMediaAttribute($product, array_keys($product->getMediaAttributes()));
        $productMediaGallery->setMediaAttribute($product, $entry->getTypes(), $filePath);
        $product->setStoreId($storeId);

        try {
            $this->productRepository->save($product);
        } catch (\Exception $exception) {
            throw new StateException(__('Cannot save product.'));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($sku, $entryId)
    {
        $product = $this->productRepository->get($sku);
        /** @var $productMediaGallery \Magento\Catalog\Model\Product\Attribute\Backend\Media */
        $productMediaGallery = $this->getGalleryAttributeBackend($product);
        $filePath = $this->entryResolver->getEntryFilePathById($product, $entryId);
        if ($filePath === null) {
            throw new NoSuchEntityException(__('There is no image with provided ID.'));
        }

        $productMediaGallery->removeImage($product, $filePath);
        $this->productRepository->save($product);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku, $imageId)
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $exception) {
            throw new NoSuchEntityException(__('Such product doesn\'t exist'));
        }

        $output = null;
        $productImages = $this->getMediaAttributeValues($product);
        foreach ((array)$product->getMediaGallery('images') as $image) {
            if (intval($image['value_id']) == intval($imageId)) {
                $image['types'] = array_keys($productImages, $image['file']);
                $output = $this->entryFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $output,
                    $image,
                    '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface'
                );
                break;
            }
        }

        if ($output === null) {
            throw new NoSuchEntityException(__('Such image doesn\'t exist'));
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($sku)
    {
        $result = [];
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);

        /** @var \Magento\Catalog\Api\Data\ProductAttributeInterface $galleryAttribute */
        $galleryAttribute = $this->attributeRepository->get('media_gallery');

        $container = new \Magento\Framework\Object(['attribute' => $galleryAttribute]);
        $gallery = $this->mediaGallery->loadGallery($product, $container);

        $productImages = $this->getMediaAttributeValues($product);

        foreach ($gallery as $image) {
            /** @var \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $entry */
            $entry = $this->entryFactory->create();
            $entry->setId($image['value_id'])
                ->setLabel($image['label_default'])
                ->setTypes(array_keys($productImages, $image['file']))
                ->setDisabled($image['disabled_default'])
                ->setPosition($image['position_default'])
                ->setFile($image['file']);
            $result[] = $entry;
        }
        return $result;
    }
}
