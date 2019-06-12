<?php
/**
<<<<<<< HEAD
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
=======
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

namespace Magento\Catalog\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Gallery\Processor;
<<<<<<< HEAD
=======
use Magento\Catalog\Model\Product\Media\Config;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
=======
     * Catalog gallery processor.
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var Processor
     */
    private $processor;

    /**
<<<<<<< HEAD
=======
     * Image content data object factory.
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var ImageContentInterfaceFactory
     */
    private $contentFactory;

    /**
<<<<<<< HEAD
=======
     * Image processor.
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var ImageProcessorInterface
     */
    private $imageProcessor;

    /**
<<<<<<< HEAD
     * MediaGalleryProcessor constructor.
     *
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
     * @param ProductInterface $product contains only existing media gallery items.
     * @param array $mediaGalleryEntries array which contains all media gallery items.
=======
     * @param ProductInterface $product contains only existing media gallery items
     * @param array $mediaGalleryEntries array which contains all media gallery items
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @return void
     * @throws InputException
     * @throws StateException
     * @throws LocalizedException
<<<<<<< HEAD
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processMediaGallery(ProductInterface $product, array $mediaGalleryEntries)
=======
     */
    public function processMediaGallery(ProductInterface $product, array $mediaGalleryEntries) :void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $existingMediaGallery = $product->getMediaGallery('images');
        $newEntries = [];
        $entriesById = [];
        if (!empty($existingMediaGallery)) {
            foreach ($mediaGalleryEntries as $entry) {
<<<<<<< HEAD
                if (isset($entry['id'])) {
                    $entriesById[$entry['id']] = $entry;
=======
                if (isset($entry['value_id'])) {
                    $entriesById[$entry['value_id']] = $entry;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                } else {
                    $newEntries[] = $entry;
                }
            }
            foreach ($existingMediaGallery as $key => &$existingEntry) {
                if (isset($entriesById[$existingEntry['value_id']])) {
                    $updatedEntry = $entriesById[$existingEntry['value_id']];
<<<<<<< HEAD
                    if (array_key_exists('file', $updatedEntry) && $updatedEntry['file'] === null) {
=======
                    if ($updatedEntry['file'] === null) {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                        unset($updatedEntry['file']);
                    }
                    $existingMediaGallery[$key] = array_merge($existingEntry, $updatedEntry);
                } else {
<<<<<<< HEAD
                    //set the removed flag.
                    $existingEntry['removed'] = true;
                }
            }
            unset($existingEntry);
=======
                    //set the removed flag
                    $existingEntry['removed'] = true;
                }
            }
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            $product->setData('media_gallery', ["images" => $existingMediaGallery]);
        } else {
            $newEntries = $mediaGalleryEntries;
        }

<<<<<<< HEAD
        $images = $product->getMediaGallery('images');

        if ($images) {
            $images = $this->determineImageRoles($product, $images);
        }

        $this->processor->clearMediaAttribute($product, array_keys($product->getMediaAttributes()));
        if ($images) {
            foreach ($images as $image) {
                if (!isset($image['removed']) && !empty($image['types'])) {
                    $this->processor->setMediaAttribute($product, $image['types'], $image['file']);
                }
            }
        }
=======
        $images = (array)$product->getMediaGallery('images');
        $images = $this->determineImageRoles($product, $images);

        $this->processor->clearMediaAttribute($product, array_keys($product->getMediaAttributes()));

        $this->processMediaAttributes($product, $images);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->processEntries($product, $newEntries, $entriesById);
    }

    /**
<<<<<<< HEAD
     * Ascertain image roles, if they are not set against the gallery entries
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     *
     * @param ProductInterface $product
     * @param array $images
     * @return array
     */
<<<<<<< HEAD
    private function determineImageRoles(ProductInterface $product, array $images)
=======
    private function determineImageRoles(ProductInterface $product, array $images) : array
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
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
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            $newEntry['content'] = $contentDataObject;
            $this->processNewMediaGalleryEntry($product, $newEntry);

            $finalGallery = $product->getData('media_gallery');
            $newEntryId = key(array_diff_key($product->getData('media_gallery')['images'], $entriesById));
<<<<<<< HEAD
            if (isset($newEntry['extension_attributes'])) {
                $this->processExtensionAttributes($newEntry, $newEntry['extension_attributes']);
            }
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            $newEntry = array_replace_recursive($newEntry, $finalGallery['images'][$newEntryId]);
            $entriesById[$newEntryId] = $newEntry;
            $finalGallery['images'][$newEntryId] = $newEntry;
            $product->setData('media_gallery', $finalGallery);
        }
    }

    /**
<<<<<<< HEAD
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
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }
}
