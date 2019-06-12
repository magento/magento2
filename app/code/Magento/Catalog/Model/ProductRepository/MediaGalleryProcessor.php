<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;

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
     * Image processor.
     *
     * @var ImageProcessorInterface
     */
    private $imageProcessor;

    /**
     * @param Processor $processor
     * @param ImageContentInterfaceFactory $contentFactory
     * @param ImageProcessorInterface $imageProcessor
     */
    public function __construct(
        Processor $processor,
        ImageContentInterfaceFactory $contentFactory,
        ImageProcessorInterface $imageProcessor
    ) {
        $this->processor = $processor;
        $this->contentFactory = $contentFactory;
        $this->imageProcessor = $imageProcessor;
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
     */
    public function processMediaGallery(ProductInterface $product, array $mediaGalleryEntries) :void
    {
        $existingMediaGallery = $product->getMediaGallery('images');
        $newEntries = [];
        $entriesById = [];
        if (!empty($existingMediaGallery)) {
            foreach ($mediaGalleryEntries as $entry) {
                if (isset($entry['value_id'])) {
                    $entriesById[$entry['value_id']] = $entry;
                } else {
                    $newEntries[] = $entry;
                }
            }
            foreach ($existingMediaGallery as $key => &$existingEntry) {
                if (isset($entriesById[$existingEntry['value_id']])) {
                    $updatedEntry = $entriesById[$existingEntry['value_id']];
                    if ($updatedEntry['file'] === null) {
                        unset($updatedEntry['file']);
                    }
                    $existingMediaGallery[$key] = array_merge($existingEntry, $updatedEntry);
                } else {
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
            $newEntryId = key(array_diff_key($product->getData('media_gallery')['images'], $entriesById));
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
            if (!isset($image['removed']) && !empty($image['types'])) {
                $this->processor->setMediaAttribute($product, $image['types'], $image['file']);
            }
        }
    }
}
