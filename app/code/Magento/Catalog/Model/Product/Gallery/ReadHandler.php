<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Catalog\Model\Product;

/**
 * Read handler for catalog product gallery.
 *
 * @api
 * @since 101.0.0
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterface
     * @since 101.0.0
     */
    protected $attribute;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     * @since 101.0.0
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
     * @since 101.0.0
     */
    protected $resourceModel;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->resourceModel = $resourceModel;
    }

    /**
     * Execute read handler for catalog product gallery
     *
     * @param Product $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 101.0.0
     */
    public function execute($entity, $arguments = [])
    {
        $mediaEntries = $this->resourceModel->loadProductGalleryByAttributeId(
            $entity,
            $this->getAttribute()->getAttributeId()
        );

        $this->addMediaDataToProduct(
            $entity,
            $this->sortMediaEntriesByPosition($mediaEntries)
        );

        return $entity;
    }

    /**
     * Add media data to product
     *
     * @param Product $product
     * @param array $mediaEntries
     * @return void
     * @since 101.0.1
     */
    public function addMediaDataToProduct(Product $product, array $mediaEntries)
    {
        $product->setData(
            $this->getAttribute()->getAttributeCode(),
            [
                'images' => array_column($mediaEntries, null, 'value_id'),
                'values' => []
            ]
        );
    }

    /**
     * Get attribute
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     * @since 101.0.0
     */
    public function getAttribute()
    {
        if (!$this->attribute) {
            $this->attribute = $this->attributeRepository->get('media_gallery');
        }

        return $this->attribute;
    }

    /**
     * Find default value
     *
     * @param string $key
     * @param string[] $image
     * @return string
     * @deprecated 101.0.1
     * @since 101.0.0
     */
    protected function findDefaultValue($key, &$image)
    {
        if (isset($image[$key . '_default'])) {
            return $image[$key . '_default'];
        }

        return '';
    }

    /**
     * Sort media entries by position
     *
     * @param array $mediaEntries
     * @return array
     */
    private function sortMediaEntriesByPosition(array $mediaEntries): array
    {
        $mediaEntriesWithNullPositions = [];
        foreach ($mediaEntries as $index => $mediaEntry) {
            if ($mediaEntry['position'] === null) {
                $mediaEntriesWithNullPositions[] = $mediaEntry;
                unset($mediaEntries[$index]);
            }
        }
        if (!empty($mediaEntries)) {
            usort(
                $mediaEntries,
                function ($entryA, $entryB) {
                    return ($entryA['position'] < $entryB['position']) ? -1 : 1;
                }
            );
        }
        return array_merge($mediaEntries, $mediaEntriesWithNullPositions);
    }
}
