<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;

/**
 * Define product's GraphQL type
 */
class Product implements HandlerInterface
{
    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @var AttributeManagementInterface
     */
    private $management;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param TypeGenerator $typeGenerator
     * @param AttributeManagementInterface $management
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        Pool $typePool,
        TypeGenerator $typeGenerator,
        AttributeManagementInterface $management,
        TypeFactory $typeFactory
    ) {
        $this->typePool = $typePool;
        $this->typeGenerator = $typeGenerator;
        $this->management = $management;
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
                            __('Type %1 does not implement Product interface', $typeId)
                        );
                    }
                    $resolvedType = $this->typePool->getComplexType(ucfirst($value['type_id']) . 'Product');

                    if (!$resolvedType) {
                        throw new InputException(
                            __('Type %1 not implemented', $value['type_id'])
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
        $attributes = $this->management->getAttributes('catalog_product', 4);
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

        $videoContent = $result['media_gallery_entries'][0]['video_content'][0];
        $content = $result['media_gallery_entries'][0]['content'][0];
        $result['media_gallery_entries'][0]['video_content'] = $videoContent;
        $result['media_gallery_entries'][0]['content'] = $content;

        $resolvedTypes = $this->typeGenerator->generate($typeName, $result);
        $fields = $resolvedTypes->config['fields'];

        return $fields;
    }
}
