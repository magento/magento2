<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Gallery\DeleteValidator;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\Store;

/**
 * Process Media gallery data for ProductRepository before save product.
 */
class MediaGalleryProcessor
{
    /**
     * Catalog gallery processor.
     *
     * @var Processor
     */
    private $processor;

    /**
     * Image content data object factory.
     *
     * @var ImageContentInterfaceFactory
     */
    private $contentFactory;

    /**
     * @var ImageProcessorInterface
     */
    private $imageProcessor;

    /**
     * @var DeleteValidator
     */
    private $deleteValidator;

    /**
     * @var Config
     */
    private $mediaConfig;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @param Processor $processor
     * @param ImageContentInterfaceFactory $contentFactory
     * @param ImageProcessorInterface $imageProcessor
     * @param DeleteValidator|null $deleteValidator
     * @param Config|null $mediaConfig
     * @param Filesystem|null $filesystem
     */
    public function __construct(
        Processor $processor,
        ImageContentInterfaceFactory $contentFactory,
        ImageProcessorInterface $imageProcessor,
        ?DeleteValidator $deleteValidator = null,
        ?Config $mediaConfig = null,
        ?Filesystem $filesystem = null
    ) {
        $this->processor = $processor;
        $this->contentFactory = $contentFactory;
        $this->imageProcessor = $imageProcessor;
        $this->deleteValidator = $deleteValidator ?? ObjectManager::getInstance()->get(DeleteValidator::class);
        $this->mediaConfig = $mediaConfig ?? ObjectManager::getInstance()->get(Config::class);
        $filesystem = $filesystem ?? ObjectManager::getInstance()->get(Filesystem::class);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Process Media gallery data before save product.
     *
     * Compare Media Gallery Entries Data with existing Media Gallery
     * * If Media entry has not value_id set it as new
     * * If Existing entry 'value_id' absent in Media Gallery set 'removed' flag
     * * Merge Existing and new media gallery
     *
     * @param ProductInterface $product contains only existing media gallery items
     * @param array $mediaGalleryEntries array which contains all media gallery items
     * @return void
     * @throws InputException
     * @throws StateException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processMediaGallery(ProductInterface $product, array $mediaGalleryEntries) :void
    {
        $existingMediaGallery = $product->getMediaGallery('images');
        $newEntries = [];
        $entriesById = [];
        $this->validateProvidedValueIds($mediaGalleryEntries, (array)$existingMediaGallery);
        if (!empty($existingMediaGallery)) {
            $existingIdByHash = $this->getExistingValueIdsByContentHash($existingMediaGallery);
            foreach ($mediaGalleryEntries as $entry) {
                if (isset($entry['value_id'])) {
                    $entriesById[$entry['value_id']] = $entry;
                } else {
                    $matchedValueId = $this->matchExistingImageByContent($entry, $existingIdByHash);
                    if ($matchedValueId !== null) {
                        // Update the existing image in place instead of creating a duplicate.
                        $entry['value_id'] = $matchedValueId;
                        $entriesById[$matchedValueId] = $entry;
                    } else {
                        $newEntries[] = $entry;
                    }
                }
            }
            foreach ($existingMediaGallery as $key => &$existingEntry) {
                $valueId = $existingEntry['value_id'] ?? null;
                if ($valueId !== null && isset($entriesById[$valueId])) {
                    $updatedEntry = $entriesById[$valueId];
                    if ($updatedEntry['file'] === null) {
                        unset($updatedEntry['file']);
                    }
                    if (isset($updatedEntry['content'])) {
                        //need to recreate image and reset object
                        $existingEntry['recreate'] = true;
                        // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                        $newEntry = array_merge($existingEntry, $updatedEntry);
                        $newEntries[] = $newEntry;
                        unset($existingMediaGallery[$key]);
                    } else {
                        // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                        $existingMediaGallery[$key] = array_merge($existingEntry, $updatedEntry);
                    }
                } elseif (!empty($newEntries) && isset($existingEntry['value_id'])) {
                    //avoid deleting an exiting image while adding a new one
                    unset($existingMediaGallery[$key]);
                } elseif ($this->canRemoveImage($product, $existingEntry)) {
                    //set the removed flag
                    $existingEntry['removed'] = true;
                }
            }
            $product->setData('media_gallery', ["images" => $existingMediaGallery]);
        } else {
            $newEntries = $mediaGalleryEntries;
        }

        $images = (array)$product->getMediaGallery('images');
        $images = $this->determineImageRoles($product, $images);

        $this->processor->clearMediaAttribute($product, array_keys($product->getMediaAttributes()));

        $this->processMediaAttributes($product, $images);
        $this->processEntries($product, $newEntries, $entriesById);
    }

    /**
     * Reject media gallery entries that reference a value_id absent from the product gallery.
     *
     * Prevents silently dropping the entry and deleting existing images when a stale or
     * unknown image ID is provided (e.g. via the REST/bulk API).
     *
     * @param array $mediaGalleryEntries
     * @param array $existingMediaGallery
     * @return void
     * @throws InputException
     */
    private function validateProvidedValueIds(array $mediaGalleryEntries, array $existingMediaGallery): void
    {
        $existingValueIds = [];
        foreach ($existingMediaGallery as $existingEntry) {
            if (isset($existingEntry['value_id'])) {
                $existingValueIds[(string)$existingEntry['value_id']] = true;
            }
        }
        foreach ($mediaGalleryEntries as $entry) {
            if (isset($entry['value_id']) && !isset($existingValueIds[(string)$entry['value_id']])) {
                throw new InputException(
                    __('The image with the "%1" ID doesn\'t exist. Verify the ID and try again.', $entry['value_id'])
                );
            }
        }
    }

    /**
     * Build a map of existing image content hashes to their value_id.
     *
     * The stored file name cannot be used for matching because Magento disambiguates
     * colliding names (image.png, image_1.png, ...), so the content hash is used instead.
     *
     * @param array $existingMediaGallery
     * @return array
     */
    private function getExistingValueIdsByContentHash(array $existingMediaGallery): array
    {
        $map = [];
        foreach ($existingMediaGallery as $existingEntry) {
            if (!isset($existingEntry['file'], $existingEntry['value_id'])) {
                continue;
            }
            $hash = $this->getStoredImageHash((string)$existingEntry['file']);
            if ($hash !== null && !isset($map[$hash])) {
                $map[$hash] = $existingEntry['value_id'];
            }
        }
        return $map;
    }

    /**
     * Calculate the content hash of an existing media gallery file.
     *
     * @param string $file
     * @return string|null
     */
    private function getStoredImageHash(string $file): ?string
    {
        try {
            $path = $this->mediaConfig->getMediaPath($file);
            if (!$this->mediaDirectory->isExist($path)) {
                return null;
            }

            return sha1((string)$this->mediaDirectory->readFile($path));
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Match a new base64 media entry to an existing image by its content hash.
     *
     * Returns the value_id of the existing image so the entry updates it in place
     * instead of being added as a duplicate.
     *
     * @param array $entry
     * @param array $existingIdByHash
     * @return int|null
     */
    private function matchExistingImageByContent(array $entry, array $existingIdByHash): ?int
    {
        $encoded = $entry['content']['data'][ImageContentInterface::BASE64_ENCODED_DATA] ?? null;
        if ($encoded === null) {
            return null;
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $decoded = base64_decode((string)$encoded, true);
        if ($decoded === false) {
            return null;
        }
        $hash = sha1($decoded);

        return isset($existingIdByHash[$hash]) ? (int)$existingIdByHash[$hash] : null;
    }

    /**
     * Process new gallery media entry.
     *
     * @param ProductInterface $product
     * @param array $newEntry
     * @return void
     * @throws InputException
     * @throws StateException
     * @throws LocalizedException
     */
    public function processNewMediaGalleryEntry(
        ProductInterface $product,
        array  $newEntry
    ) :void {
        /** @var ImageContentInterface $contentDataObject */
        $contentDataObject = $newEntry['content'];

        /** @var Config $mediaConfig */
        $mediaConfig = $product->getMediaConfig();
        $mediaTmpPath = $mediaConfig->getBaseTmpMediaPath();

        $relativeFilePath = $this->imageProcessor->processImageContent($mediaTmpPath, $contentDataObject);
        $tmpFilePath = $mediaConfig->getTmpMediaShortUrl($relativeFilePath);

        if (!$product->hasGalleryAttribute()) {
            throw new StateException(
                __("The product that was requested doesn't exist. Verify the product and try again.")
            );
        }

        $imageFileUri = $this->processor->addImage(
            $product,
            $tmpFilePath,
            isset($newEntry['types']) ? $newEntry['types'] : [],
            true,
            isset($newEntry['disabled']) ? $newEntry['disabled'] : true
        );
        // Update additional fields that are still empty after addImage call
        $this->processor->updateImage(
            $product,
            $imageFileUri,
            [
                'label' => $newEntry['label'],
                'position' => $newEntry['position'],
                'disabled' => $newEntry['disabled'],
                'media_type' => $newEntry['media_type'],
            ]
        );
    }

    /**
     * Ascertain image roles, if they are not set against the gallery entries.
     *
     * @param ProductInterface $product
     * @param array $images
     * @return array
     */
    private function determineImageRoles(ProductInterface $product, array $images) : array
    {
        $imagesWithRoles = [];
        foreach ($images as $image) {
            if (!isset($image['types'])) {
                $image['types'] = [];
                if (isset($image['file'])) {
                    foreach (array_keys($product->getMediaAttributes()) as $attribute) {
                        if ($image['file'] == $product->getData($attribute)) {
                            $image['types'][] = $attribute;
                        }
                    }
                }
            }
            $imagesWithRoles[] = $image;
        }

        return $imagesWithRoles;
    }

    /**
     * Convert entries into product media gallery data and set to product.
     *
     * @param ProductInterface $product
     * @param array $newEntries
     * @param array $entriesById
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
    private function processEntries(ProductInterface $product, array $newEntries, array $entriesById): void
    {
        foreach ($newEntries as $newEntry) {
            if (!isset($newEntry['content'])) {
                throw new InputException(__('The image content is invalid. Verify the content and try again.'));
            }
            /** @var ImageContentInterface $contentDataObject */
            $contentDataObject = $this->contentFactory->create()
                ->setName($newEntry['content']['data'][ImageContentInterface::NAME])
                ->setBase64EncodedData($newEntry['content']['data'][ImageContentInterface::BASE64_ENCODED_DATA])
                ->setType($newEntry['content']['data'][ImageContentInterface::TYPE]);
            $newEntry['content'] = $contentDataObject;
            $this->processNewMediaGalleryEntry($product, $newEntry);

            $finalGallery = $product->getData('media_gallery');

            $entryIds = array_keys(
                array_diff_key(
                    $product->getData('media_gallery')['images'],
                    $entriesById
                )
            );
            $newEntryId = array_pop($entryIds);

            $newEntry = array_replace_recursive($newEntry, $finalGallery['images'][$newEntryId]);
            $entriesById[$newEntryId] = $newEntry;
            $finalGallery['images'][$newEntryId] = $newEntry;
            $product->setData('media_gallery', $finalGallery);
        }
    }

    /**
     * Set media attribute values.
     *
     * @param ProductInterface $product
     * @param array $images
     */
    private function processMediaAttributes(ProductInterface $product, array $images): void
    {
        foreach ($images as $image) {
            if (empty($image['removed']) && !empty($image['types'])) {
                $this->processor->setMediaAttribute($product, $image['types'], $image['file']);
            }
        }
    }

    /**
     * Check whether the image can be removed
     *
     * @param ProductInterface $product
     * @param array $image
     * @return bool
     */
    private function canRemoveImage(ProductInterface $product, array $image): bool
    {
        return !isset($image['file'])
            || !$this->deleteValidator->validate($product, $image['file']);
    }
}
