<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadataApi\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Api\ExtractMetadataInterface;

/**
 * Metadata extractor composite
 */
class ExtractMetadataComposite implements ExtractMetadataInterface
{
    /**
     * @var ExtractMetadataInterface[]
     */
    private $extractors;

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataFactory;

    /**
     * @param MetadataInterfaceFactory $metadataFactory
     * @param ExtractMetadataInterface[] $extractors
     */
    public function __construct(
        MetadataInterfaceFactory $metadataFactory,
        array $extractors
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->extractors = $extractors;
    }

    /**
     * Extract metadata from file
     *
     * @param string $path
     * @return MetadataInterface
     * @throws LocalizedException
     */
    public function execute(string $path): MetadataInterface
    {
        $title = null;
        $description = null;
        $keywords = [];

        foreach ($this->extractors as $extractor) {
            if (!$extractor instanceof ExtractMetadataInterface) {
                throw new \InvalidArgumentException(
                    __(get_class($extractor) . ' must implement ' . ExtractMetadataInterface::class)
                );
            }

            $data = $extractor->execute($path);
            $title = !empty($data->getTitle()) ? $data->getTitle() : $title;
            $description = !empty($data->getDescription()) ? $data->getDescription() : $description;

            if (!empty($data->getKeywords())) {
                foreach ($data->getKeywords() as $keyword) {
                    $keywords[] = $keyword;
                }
            }
        }
        return $this->metadataFactory->create([
            'title' => $title,
            'description' => $description,
            'keywords' => empty($keywords) ? null : array_unique($keywords)
        ]);
    }
}
