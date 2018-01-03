<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\GraphQl\Model\EntityAttributeList;
use Magento\Framework\Exception\InputException;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;
use Magento\GraphQlEav\Model\Resolver\Query\Type;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

/**
 * Define product's GraphQL type
 */
class Product implements HandlerInterface
{
    const PRODUCT_TYPE_NAME = 'Product';

    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @var EntityAttributeList
     */
    private $entityAttributeList;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var Type
     */
    private $typeLocator;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @param Pool $typePool
     * @param TypeGenerator $typeGenerator
     * @param EntityAttributeList $entityAttributeList
     * @param TypeFactory $typeFactory
     * @param Type $typeLocator
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        Pool $typePool,
        TypeGenerator $typeGenerator,
        EntityAttributeList $entityAttributeList,
        TypeFactory $typeFactory,
        Type $typeLocator,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->typePool = $typePool;
        $this->typeGenerator = $typeGenerator;
        $this->entityAttributeList = $entityAttributeList;
        $this->typeFactory = $typeFactory;
        $this->typeLocator = $typeLocator;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        $typeName = self::PRODUCT_TYPE_NAME;
        return  $this->typeFactory->createInterface(
            [
                'name' => $typeName,
                'fields' => $this->getFields($typeName),
                'resolveType' => function ($value) {
                    $typeId = $value['type_id'];
                    if (!in_array($typeId, ['simple', 'configurable'])) {
                        throw new InputException(
                            __('Type %1 does not implement %2 interface', $typeId, self::PRODUCT_TYPE_NAME)
                        );
                    }
                    $resolvedType = $this->typePool->getType(ucfirst($typeId) . self::PRODUCT_TYPE_NAME);

                    if (!$resolvedType) {
                        throw new InputException(
                            __('Type %1 not implemented', $typeId)
                        );
                    }

                    return $resolvedType;
                }
            ]
        );
    }

    /**
     * Retrieve Product base fields
     *
     * @param string $typeName
     * @return array
     * @throws \LogicException Schema failed to generate from service contract type name
     * @throws GraphQlInputException Entity has invalid type unable to resolve to GraphQL type
     */
    private function getFields(string $typeName)
    {
        $result = [];
        $productEntityType = \Magento\Catalog\Model\Product::ENTITY;
        $attributes = $this->entityAttributeList->getDefaultEntityAttributes(
            $productEntityType,
            $this->productAttributeRepository
        );
        foreach (array_keys($attributes) as $attributeCode) {
            $locatedType = $this->typeLocator->getType(
                $attributeCode,
                $productEntityType
            ) ?: 'string';
            $locatedType = $locatedType === TypeProcessor::NORMALIZED_ANY_TYPE ? 'string' : $locatedType;
            $result[$attributeCode] = $locatedType;
        }

        $staticAttributes = $this->typeGenerator->getTypeData('CatalogDataProductInterface');
        $result = array_merge($result, $staticAttributes);

        unset($result['stock_item']);
        unset($result['bundle_product_options']);
        unset($result['downloadable_product_links']);
        unset($result['downloadable_product_samples']);
        unset($result['configurable_product_links']);
        unset($result['quantity_and_stock_status']);
        unset($result['sku_type']);

        $mediaGalleryEntries = current($result['media_gallery_entries']);
        $videoContent = $mediaGalleryEntries['video_content'];
        $content = $mediaGalleryEntries['content'];
        $result['media_gallery_entries'][0]['video_content'] = $videoContent;
        $result['media_gallery_entries'][0]['content'] = $content;
        $result['category_ids'] = ['int'];
        $result['media_gallery'] =
            [
                'images' => [
                    0 => [
                        'value_id' => 'int',
                        'file' => 'string',
                        'media_type' => 'string',
                        'entity_id' => 'string',
                        'label' => 'string',
                        'position' => 'int',
                        'disabled' => 'string',
                        'label_default' => 'string',
                        'position_default' => 'string',
                        'disabled_default' => 'string',
                        'video_provider' => 'string',
                        'video_url' => 'string',
                        'video_description' => 'string',
                        'video_metadata' => 'string',
                        'video_provider_default' => 'string',
                        'video_url_default' => 'string',
                        'video_title_default' => 'string',
                        'video_description_default' => 'string',
                        'video_metadata_default' => 'string'
                    ]
                ]
            ];

        $result['price'] = ProductPrice::PRODUCT_PRICE_TYPE_NAME;
        unset($result['minimal_price']);

        $resolvedTypes = $this->typeGenerator->generate($typeName, $result);
        $fields = $resolvedTypes->config['fields'];

        return $fields;
    }
}
