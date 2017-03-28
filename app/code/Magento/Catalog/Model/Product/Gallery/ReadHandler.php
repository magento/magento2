<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Catalog\Model\Product;

/**
 * Read handler for catalog product gallery.
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    protected $attribute;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
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
     * @param Product $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $value = [];
        $value['images'] = [];

        $mediaEntries = $this->resourceModel->loadProductGalleryByAttributeId(
            $entity,
            $this->getAttribute()->getAttributeId()
        );

        $this->addMediaDataToProduct(
            $entity,
            $mediaEntries
        );
        
        return $entity;
    }

    /**
     * @param Product $product
     * @param array $mediaEntries
     * @return void
     */
    public function addMediaDataToProduct(Product $product, array $mediaEntries)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = [];
        $value['images'] = [];
        $value['values'] = [];

        foreach ($mediaEntries as $mediaEntry) {
            $mediaEntry = $this->substituteNullsWithDefaultValues($mediaEntry);
            $value['images'][$mediaEntry['value_id']] = $mediaEntry;
        }
        $product->setData($attrCode, $value);
    }

    /**
     * @param array $rawData
     * @return array
     */
    private function substituteNullsWithDefaultValues(array $rawData)
    {
        $processedData = [];
        foreach ($rawData as $key => $rawValue) {
            if (null !== $rawValue) {
                $processedValue = $rawValue;
            } elseif (isset($rawData[$key . '_default'])) {
                $processedValue = $rawData[$key . '_default'];
            } else {
                $processedValue = null;
            }
            $processedData[$key] = $processedValue;
        }

        return $processedData;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    public function getAttribute()
    {
        if (!$this->attribute) {
            $this->attribute = $this->attributeRepository->get('media_gallery');
        }

        return $this->attribute;
    }

    /**
     * @param string $key
     * @param string[] &$image
     * @return string
     * @deprecated
     */
    protected function findDefaultValue($key, &$image)
    {
        if (isset($image[$key . '_default'])) {
            return $image[$key . '_default'];
        }

        return '';
    }
}
