<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

/**
 * Read handler for catalog product gallery.
 */
class ReadHandler
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
     * @param string $entityType
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $product)
    {
        $value = [];
        $value['images'] = [];

        $localAttributes = ['label', 'position', 'disabled'];

        $mediaEntries = $this->resourceModel->loadProductGalleryByAttributeId(
            $product,
            $this->getAttribute()->getAttributeId()
        );

        foreach ($mediaEntries as $mediaEntry) {
            foreach ($localAttributes as $localAttribute) {
                if ($mediaEntry[$localAttribute] === null) {
                    $mediaEntry[$localAttribute] = $this->findDefaultValue($localAttribute, $mediaEntry);
                }
            }

            $value['images'][] = $mediaEntry;
        }

        $product->setData(
            $this->getAttribute()->getAttributeCode(),
            $value
        );

        return $product;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    public function getAttribute()
    {
        if (!$this->attribute) {
            $this->attribute = $this->attributeRepository->get(
                'media_gallery'
            );
        }

        return $this->attribute;
    }

    /**
     * @param string $key
     * @param string[] &$image
     * @return string
     */
    protected function findDefaultValue($key, &$image)
    {
        if (isset($image[$key . '_default'])) {
            return $image[$key . '_default'];
        }

        return '';
    }
}
