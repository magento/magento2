<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Gallery\Processor;
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
     * @var Processor
     */
    private $processor;

    /**
     * @var ImageContentInterfaceFactory
     */
    private $contentFactory;

    /**
     * @var ImageProcessorInterface
     */
    private $imageProcessor;

    /**
     * MediaGalleryProcessor constructor.
     *
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
     * @param ProductInterface $product contains only existing media gallery items.
     * @param array $mediaGalleryEntries array which contains all media gallery items.
     * @return void
     * @throws InputException
     * @throws StateException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processMediaGallery(ProductInterface $product, array $mediaGalleryEntries)
    {
        $existingMediaGallery = $product->getMediaGallery('images');
        $newEntries = [];
        $entriesById = [];
        if (!empty($existingMediaGallery)) {
            foreach ($mediaGalleryEntries as $entry) {
                if (isset($entry['id'])) {
                    $entriesById[$entry['id']] = $entry;
                } else {
                    $newEntries[] = $entry;
                }
            }
            foreach ($existingMediaGallery as $key => &$existingEntry) {
                if (isset($entriesById[$existingEntry['value_id']])) {
                    $updatedEntry = $entriesById[$existingEntry['value_id']];
                    if (array_key_exists('file', $updatedEntry) && $updatedEntry['file'] === null) {
                        unset($updatedEntry['file']);
                    }
                    $existingMediaGallery[$key] = array_merge($existingEntry, $updatedEntry);
                } else {
                    //set the removed flag.
                    $existingEntry['removed'] = true;
                }
            }
            unset($existingEntry);
            $product->setData('media_gallery', ["images" => $existingMediaGallery]);
        } else {
            $newEntries = $mediaGalleryEntries;
        }

        $this->processor->clearMediaAttribute($product, array_keys($product->getMediaAttributes()));
        $images = $product->getMediaGallery('images');
        if ($images) {
            foreach ($images as $image) {
                if (!isset($image['removed']) && !empty($image['types'])) {
                    $this->processor->setMediaAttribute($product, $image['types'], $image['file']);
                }
            }
        }
        $this->processEntries($product, $newEntries, $entriesById);
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
     * @return void
     */
    private function processEntries(ProductInterface $product, array $newEntries, array $entriesById)
    {
        foreach ($newEntries as $newEntry) {
            if (!isset($newEntry['content'])) {
                throw new InputException(__('The image content is not valid.'));
            }
            /** @var ImageContentInterface $contentDataObject */
            $contentDataObject = $this->contentFactory->create()
                ->setName($newEntry['content'][ImageContentInterface::NAME])
                ->setBase64EncodedData($newEntry['content'][ImageContentInterface::BASE64_ENCODED_DATA])
                ->setType($newEntry['content'][ImageContentInterface::TYPE]);
            $newEntry['content'] = $contentDataObject;
            $this->processNewMediaGalleryEntry($product, $newEntry);

            $finalGallery = $product->getData('media_gallery');
            $newEntryId = key(array_diff_key($product->getData('media_gallery')['images'], $entriesById));
            if (isset($newEntry['extension_attributes'])) {
                $this->processExtensionAttributes($newEntry, $newEntry['extension_attributes']);
            }
            $newEntry = array_replace_recursive($newEntry, $finalGallery['images'][$newEntryId]);
            $entriesById[$newEntryId] = $newEntry;
            $finalGallery['images'][$newEntryId] = $newEntry;
            $product->setData('media_gallery', $finalGallery);
        }
    }

    /**
     * Save gallery entry as image.
     *
     * @param ProductInterface $product
     * @param array $newEntry
     * @return void
     * @throws InputException
     * @throws StateException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processNewMediaGalleryEntry(
        ProductInterface $product,
        array $newEntry
    ) {
        /** @var ImageContentInterface $contentDataObject */
        $contentDataObject = $newEntry['content'];

        /** @var \Magento\Catalog\Model\Product\Media\Config $mediaConfig */
        $mediaConfig = $product->getMediaConfig();
        $mediaTmpPath = $mediaConfig->getBaseTmpMediaPath();

        $relativeFilePath = $this->imageProcessor->processImageContent($mediaTmpPath, $contentDataObject);
        $tmpFilePath = $mediaConfig->getTmpMediaShortUrl($relativeFilePath);

        if (!$product->hasGalleryAttribute()) {
            throw new StateException(__('Requested product does not support images.'));
        }

        $imageFileUri = $this->processor->addImage(
            $product,
            $tmpFilePath,
            isset($newEntry['types']) ? $newEntry['types'] : [],
            true,
            isset($newEntry['disabled']) ? $newEntry['disabled'] : true
        );
        // Update additional fields that are still empty after addImage call.
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
     * Convert extension attribute for product media gallery.
     *
     * @param array $newEntry
     * @param array $extensionAttributes
     * @return void
     */
    private function processExtensionAttributes(array &$newEntry, array $extensionAttributes)
    {
        foreach ($extensionAttributes as $code => $value) {
            if (is_array($value)) {
                $this->processExtensionAttributes($newEntry, $value);
            } else {
                $newEntry[$code] = $value;
            }
        }
        unset($newEntry['extension_attributes']);
    }
}
