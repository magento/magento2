<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\ServiceOutputProcessor;

/**
 * Media gallery field resolver, used for GraphQL request processing.
 */
class MediaGalleryEntries
{
    /**
     * @var ProductAttributeMediaGalleryManagementInterface
     */
    private $mediaGalleryManagement;

    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @param ProductAttributeMediaGalleryManagementInterface $mediaGalleryManagement
     * @param ServiceOutputProcessor $serviceOutputProcessor
     */
    public function __construct(
        ProductAttributeMediaGalleryManagementInterface $mediaGalleryManagement,
        ServiceOutputProcessor $serviceOutputProcessor
    ) {
        $this->mediaGalleryManagement = $mediaGalleryManagement;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
    }

    /**
     * Get media gallery entries for the specified product.
     *
     * @param string $sku
     * @return array|null
     */
    public function getMediaGalleryEntries(string $sku)
    {
        try {
            $mediaGalleryObjectArray = $this->mediaGalleryManagement->getList($sku);
        } catch (NoSuchEntityException $e) {
            // No error should be thrown, null result should be returned
            return null;
        }

        $mediaGalleryList = $this->serviceOutputProcessor->process(
            $mediaGalleryObjectArray,
            ProductAttributeMediaGalleryManagementInterface::class,
            'getList'
        );

        foreach ($mediaGalleryList as $key => $mediaGallery) {
            if (isset($mediaGallery[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])
                && isset($mediaGallery[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['video_content'])) {
                $mediaGallery = array_merge(
                    $mediaGallery,
                    $mediaGallery[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
                );
                $mediaGalleryList[$key] = $mediaGallery;
            }
        }

        return $mediaGalleryList;
    }
}
