<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File\Mime;

/**
 * Converter for Image media gallery type
 */
class ImageEntryConverter implements EntryConverterInterface
{
    /**
     * Media Entry type code
     */
    const MEDIA_TYPE_CODE = 'image';

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory
     */
    protected $mediaGalleryEntryFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var ImageContentInterfaceFactory
     */
    protected $imageContentInterface;

    /**
     * Filesystem facade
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Mime
     */
    protected $imageMime;

    /**
     * @param ProductAttributeMediaGalleryEntryInterfaceFactory $mediaGalleryEntryFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ImageContentInterfaceFactory|null $imageContentInterface
     * @param Filesystem|null $filesystem
     * @param Mime|null $imageMime
     */
    public function __construct(
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory $mediaGalleryEntryFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        ImageContentInterfaceFactory $imageContentInterface = null,
        Filesystem $filesystem = null,
        Mime $imageMime = null

    ) {
        $this->mediaGalleryEntryFactory = $mediaGalleryEntryFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->imageContentInterface = $imageContentInterface ?? ObjectManager::getInstance()->get(ImageContentInterfaceFactory::class);
        $this->filesystem =  $filesystem ?? ObjectManager::getInstance()->get(Filesystem::class);
        $this->imageMime = $imageMime ?? ObjectManager::getInstance()->get(Mime::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaEntryType()
    {
        return self::MEDIA_TYPE_CODE;
    }

    /**
     * @param Product $product
     * @param array $rowData
     * @return ProductAttributeMediaGalleryEntryInterface $entry
     */
    public function convertTo(Product $product, array $rowData)
    {
        $image = $rowData;
        $productImages = $product->getMediaAttributeValues();
        if (!isset($image['types'])) {
            $image['types'] = array_keys($productImages, $image['file']);
        }
        $entry = $this->mediaGalleryEntryFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $entry,
            $image,
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
        );
        if (isset($image['value_id'])) {
            $entry->setId($image['value_id']);
        }
        $imageFileContent = file_get_contents($product->getMediaConfig()->getMediaUrl(($entry->getFile())));
        $entryContent = $this->imageContentInterface->create()
            ->setName(basename($entry->getFile()))
            ->setBase64EncodedData(base64_encode($imageFileContent))
            ->setType($this->getImageMimeType($product,$entry));
        $entry->setContent($entryContent);
        return $entry;
    }

    /**
     * @throws FileSystemException
     */
    public function getImageMimeType($product, $entry)
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $path = $directory->getAbsolutePath($product->getMediaConfig()->getMediaPath($entry->getFile()));
        return $this->imageMime->getMimeType($path);
    }

    /**
     * @param ProductAttributeMediaGalleryEntryInterface $entry
     * @return array
     */
    public function convertFrom(ProductAttributeMediaGalleryEntryInterface $entry)
    {
        $entryArray = [
            'value_id' => $entry->getId(),
            'file' => $entry->getFile(),
            'label' => $entry->getLabel(),
            'position' => $entry->getPosition(),
            'disabled' => $entry->isDisabled(),
            'types' => $entry->getTypes(),
            'media_type' => $entry->getMediaType(),
            'content' => $this->convertFromMediaGalleryEntryContentInterface($entry->getContent()),
        ];
        return $entryArray;
    }

    /**
     * @param ImageContentInterface $content
     * @return array
     */
    protected function convertFromMediaGalleryEntryContentInterface(
        ImageContentInterface $content = null
    ) {
        if ($content === null) {
            return null;
        }

        return [
            'data' => [
                ImageContentInterface::BASE64_ENCODED_DATA => $content->getBase64EncodedData(),
                ImageContentInterface::TYPE => $content->getType(),
                ImageContentInterface::NAME => $content->getName(),
            ],
        ];
    }
}
