<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;

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
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $value = [];
        $value['images'] = [];

        $localAttributes = ['label', 'position', 'disabled'];

        $mediaEntries = $this->resourceModel->loadProductGalleryByAttributeId(
            $entity,
            $this->getAttribute()->getAttributeId()
        );

        foreach ($mediaEntries as $mediaEntry) {
            foreach ($localAttributes as $localAttribute) {
                if ($mediaEntry[$localAttribute] === null) {
                    $mediaEntry[$localAttribute] = $this->findDefaultValue($localAttribute, $mediaEntry);
                }
            }

            $value['images'][$mediaEntry['value_id']] = $mediaEntry;
        }

        $entity->setData(
            $this->getAttribute()->getAttributeCode(),
            $value
        );

        return $entity;
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
