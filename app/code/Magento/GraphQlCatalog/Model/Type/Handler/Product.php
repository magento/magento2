<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\GraphQl\Model\EntityAttributeList;
use Magento\Framework\Exception\InputException;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;

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
     * @param Pool $typePool
     * @param TypeGenerator $typeGenerator
     * @param EntityAttributeList $entityAttributeList
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        Pool $typePool,
        TypeGenerator $typeGenerator,
        EntityAttributeList $entityAttributeList,
        TypeFactory $typeFactory
    ) {
        $this->typePool = $typePool;
        $this->typeGenerator = $typeGenerator;
        $this->entityAttributeList = $entityAttributeList;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return  $this->typeFactory->createInterface(
            [
                'name' => $reflector->getShortName(),
                'fields' => $this->getFields($reflector->getShortName()),
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
     */
    private function getFields(string $typeName)
    {
        $result = [];
        $attributes = $this->entityAttributeList->getDefaultEntityAttributes(\Magento\Catalog\Model\Product::ENTITY);
        foreach ($attributes as $attribute) {
            $result[$attribute->getAttributeCode()] = 'string';
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

        $resolvedTypes = $this->typeGenerator->generate($typeName, $result);
        $fields = $resolvedTypes->config['fields'];

        return $fields;
    }
}
