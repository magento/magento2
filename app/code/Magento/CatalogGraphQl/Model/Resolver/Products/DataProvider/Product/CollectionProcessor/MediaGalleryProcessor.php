<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;

/**
 * Add attributes required for every GraphQL product resolution process.
 *
 * {@inheritdoc}
 */
class MediaGalleryProcessor implements CollectionProcessorInterface
{
    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * Add media gallery attributes to collection
     *
     * @param MediaConfig $mediaConfig
     */
    public function __construct(MediaConfig $mediaConfig)
    {
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @inheritdoc
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames
    ): Collection {
        if (in_array('media_gallery_entries', $attributeNames)) {
            $mediaAttributes = $this->mediaConfig->getMediaAttributeCodes();
            foreach ($mediaAttributes as $mediaAttribute) {
                if (!in_array($mediaAttribute, $attributeNames)) {
                    $collection->addAttributeToSelect($mediaAttribute);
                }
            }
        }

        return $collection;
    }
}
