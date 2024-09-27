<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\AwsS3\Driver\AwsS3;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File\Mime;
use Magento\Framework\Filesystem\Io\File;

/**
 * Class GalleryManagement
 *
 * Provides implementation of api interface ProductAttributeMediaGalleryManagementInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GalleryManagement implements \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ImageContentValidatorInterface
     */
    protected $contentValidator;

    /**
     * @var ProductInterfaceFactory
     */
    private $productInterfaceFactory;

    /**
     * @var DeleteValidator
     */
    private $deleteValidator;

    /**
     * @var ImageContentInterfaceFactory
     */
    private $imageContentInterface;

    /**
     * Filesystem facade
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var File
     */
    private $file;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ImageContentValidatorInterface $contentValidator
     * @param ProductInterfaceFactory|null $productInterfaceFactory
     * @param DeleteValidator|null $deleteValidator
     * @param ImageContentInterfaceFactory|null $imageContentInterface
     * @param Filesystem|null $filesystem
     * @param Mime|null $mime
     * @param File|null $file
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ImageContentValidatorInterface $contentValidator,
        ?ProductInterfaceFactory $productInterfaceFactory = null,
        ?DeleteValidator $deleteValidator = null,
        ?ImageContentInterfaceFactory $imageContentInterface = null,
        ?Filesystem $filesystem = null,
        ?Mime $mime = null,
        ?File $file = null
    ) {
        $this->productRepository = $productRepository;
        $this->contentValidator = $contentValidator;
        $this->productInterfaceFactory = $productInterfaceFactory
            ?? ObjectManager::getInstance()->get(ProductInterfaceFactory::class);
        $this->deleteValidator = $deleteValidator
            ?? ObjectManager::getInstance()->get(DeleteValidator::class);
        $this->imageContentInterface = $imageContentInterface
            ?? ObjectManager::getInstance()->get(ImageContentInterfaceFactory::class);
        $this->filesystem =  $filesystem
            ?? ObjectManager::getInstance()->get(Filesystem::class);
        $this->mime = $mime
            ?? ObjectManager::getInstance()->get(Mime::class);
        $this->file = $file
            ?? ObjectManager::getInstance()->get(
                File::class
            );
    }

    /**
     * @inheritdoc
     */
    public function create($sku, ProductAttributeMediaGalleryEntryInterface $entry)
    {
        /** @var $entry ProductAttributeMediaGalleryEntryInterface */
        $entryContent = $entry->getContent();

        if (!$this->contentValidator->isValid($entryContent)) {
            throw new InputException(__('The image content is invalid. Verify the content and try again.'));
        }
        $product = $this->productRepository->get($sku, true);

        $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
        $existingEntryIds = [];
        if ($existingMediaGalleryEntries == null) {
            // set all media types if not specified
            if ($entry->getTypes() == null) {
                $entry->setTypes(array_keys($product->getMediaAttributes()));
            }
            $existingMediaGalleryEntries = [$entry];
        } else {
            foreach ($existingMediaGalleryEntries as $existingEntries) {
                $existingEntryIds[$existingEntries->getId()] = $existingEntries->getId();
            }
            $existingMediaGalleryEntries[] = $entry;
        }
        $product = $this->productInterfaceFactory->create();
        $product->setSku($sku);
        $product->setMediaGalleryEntries($existingMediaGalleryEntries);
        try {
            $product = $this->productRepository->save($product);
        } catch (\Exception $e) {
            if ($e instanceof InputException) {
                throw $e;
            } else {
                throw new StateException(__("The product can't be saved."));
            }
        }

        foreach ($product->getMediaGalleryEntries() as $entry) {
            if (!isset($existingEntryIds[$entry->getId()])) {
                return $entry->getId();
            }
        }
        throw new StateException(__('The new media gallery entry failed to save.'));
    }

    /**
     * @inheritdoc
     */
    public function update($sku, ProductAttributeMediaGalleryEntryInterface $entry)
    {
        $product = $this->productRepository->get($sku, true);
        $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
        if ($existingMediaGalleryEntries == null) {
            throw new NoSuchEntityException(
                __('No image with the provided ID was found. Verify the ID and try again.')
            );
        }
        $found = false;
        $entryTypes = (array)$entry->getTypes();
        foreach ($existingMediaGalleryEntries as $key => $existingEntry) {
            $existingEntryTypes = (array)$existingEntry->getTypes();
            $existingEntry->setTypes(array_diff($existingEntryTypes, $entryTypes));

            if ($existingEntry->getId() == $entry->getId()) {
                $found = true;
                $existingMediaGalleryEntries[$key] = $entry;
            }
        }
        if (!$found) {
            throw new NoSuchEntityException(
                __('No image with the provided ID was found. Verify the ID and try again.')
            );
        }
        $product = $this->productInterfaceFactory->create();
        $product->setSku($sku);
        $product->setMediaGalleryEntries($existingMediaGalleryEntries);

        try {
            $this->productRepository->save($product);
        } catch (\Exception $exception) {
            throw new StateException(__("The product can't be saved."));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function remove($sku, $entryId)
    {
        $product = $this->productRepository->get($sku, true);
        $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
        if ($existingMediaGalleryEntries == null) {
            throw new NoSuchEntityException(
                __('No image with the provided ID was found. Verify the ID and try again.')
            );
        }
        $found = false;
        foreach ($existingMediaGalleryEntries as $key => $entry) {
            if ($entry->getId() == $entryId) {
                unset($existingMediaGalleryEntries[$key]);
                $errors = $this->deleteValidator->validate($product, $entry->getFile());
                if (!empty($errors)) {
                    throw new StateException($errors[0]);
                }
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw new NoSuchEntityException(
                __('No image with the provided ID was found. Verify the ID and try again.')
            );
        }
        $product = $this->productInterfaceFactory->create();
        $product->setSku($sku);
        $product->setMediaGalleryEntries($existingMediaGalleryEntries);
        $this->productRepository->save($product);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function get($sku, $entryId)
    {
        try {
            /** @var Product $product */
            $product = $this->productRepository->get($sku);
        } catch (\Exception $exception) {
            throw new NoSuchEntityException(__("The product doesn't exist. Verify and try again."));
        }

        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        foreach ($mediaGalleryEntries as $entry) {
            if ($entry->getId() == $entryId) {
                $entry->setContent($this->getImageContent($product, $entry));
                return $entry;
            }
        }

        throw new NoSuchEntityException(__("The image doesn't exist. Verify and try again."));
    }

    /**
     * @inheritdoc
     */
    public function getList($sku)
    {
        /** @var Product $product */
        $product = $this->productRepository->get($sku);
        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        foreach ($mediaGalleryEntries as $entry) {
            $entry->setContent($this->getImageContent($product, $entry));
        }
        return $mediaGalleryEntries;
    }

    /**
     * Get image content
     *
     * @param Product $product
     * @param ProductAttributeMediaGalleryEntryInterface $entry
     * @return ImageContentInterface
     * @throws FileSystemException
     */
    private function getImageContent($product, $entry): ImageContentInterface
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $path = $mediaDirectory->getAbsolutePath($product->getMediaConfig()->getMediaPath($entry->getFile()));
        $fileName = $this->file->getPathInfo($path)['basename'];
        $fileDriver = $mediaDirectory->getDriver();
        $imageFileContent = $fileDriver->fileGetContents($path);

        if ($fileDriver instanceof AwsS3) {
            $remoteMediaMimeType = $fileDriver->getMetadata($path);
            $mediaMimeType = $remoteMediaMimeType['mimetype'];
        } else {
            $mediaMimeType = $this->mime->getMimeType($path);
        }
        return $this->imageContentInterface->create()
            ->setName($fileName)
            ->setBase64EncodedData(base64_encode($imageFileContent))
            ->setType($mediaMimeType);
    }
}
