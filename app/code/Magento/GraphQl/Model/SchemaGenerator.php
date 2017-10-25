<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils\BuildSchema;
use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQl\Model\Resolver\Product;

/**
 * GraphQl schema generator.
 *
 * TODO: This is temporary implementation which must be replaced
 */
class SchemaGenerator implements SchemaGeneratorInterface
{
    /**
     * @var string[]
     */
    private $generatedTypes = [];

    /**
     * @var Product
     */
    private $productResolver;

    /**
     * Initialize dependencies
     *
     * @param Product $productResolver
     */
    public function __construct(
        Product $productResolver
    ) {
        $this->productResolver = $productResolver;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function generate()
    {
        $typeConfigDecorator = function ($typeConfig, $typeDefinitionNode) {
            $name = $typeConfig['name'];
            if ($name == 'Query') {
                $typeConfig['resolveField'] = function ($value, $args, $context, ResolveInfo $info) {
                    if (empty($args['sku']) || !is_string($args['sku'])) {
                        throw new LocalizedException(__('SKU is a required argument in a string format'));
                    }

                    $productData = $this->productResolver->getProduct($args['sku']);
                    switch ($info->fieldName) {
                        case 'product':
                            return $productData;
                        default:
                            return null;
                    }
                };
            }

            if ($name === 'AbstractProduct') {
                $typeConfig['resolveType'] = function ($objectValue, $context, $info) {
                    switch ($objectValue['type_id']) {
                        case 'configurable':
                            return 'ConfigurableProduct';
                        case 'simple':
                            return 'SimpleProduct';
                        default:
                            return null;
                    }
                };
            }
            return $typeConfig;
        };

        return BuildSchema::build($this->readSchema(), $typeConfigDecorator);
    }

    /**
     * Gets type data from string
     *
     * @param string $type
     * @return array
     * @throws \Exception
     */
    public function getTypeData($type)
    {
        /** @var \Magento\Framework\Reflection\TypeProcessor $typeProcessor */
        $typeProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Reflection\TypeProcessor::class
        );
        $typesData = $typeProcessor->getTypeData($type);

        $result = [];
        if (isset($typesData['parameters'])) {
            foreach ($typesData['parameters'] as $attributeCode => $parameter) {
                $snakeAttributeCode = \Magento\Framework\Api\SimpleDataObjectConverter::camelCaseToSnakeCase(
                    $attributeCode
                );

                if ($snakeAttributeCode == 'custom_attributes') {
                    continue;
                }

                if ($typeProcessor->isTypeAny($parameter['type'])) {
                    throw new \Exception("Mixed type detected");
                } elseif ($typeProcessor->isArrayType($parameter['type'])) {
                    $arrayItemType = $typeProcessor->getArrayItemType($parameter['type']);
                    if ($typeProcessor->isTypeSimple($arrayItemType)) {
                        $result[$snakeAttributeCode][] = $arrayItemType;
                    } else {
                        $result[$snakeAttributeCode][] = $this->getTypeData($arrayItemType);
                    }
                } elseif ($typeProcessor->isTypeSimple($parameter['type'])) {
                    $result[$snakeAttributeCode] = $parameter['type'];
                } else {
                    if ($snakeAttributeCode == 'extension_attributes') {
                        $extensionAttributes = $this->getTypeData($parameter['type']);
                        $result = array_merge($result, $extensionAttributes);
                    } else {
                        $result[$snakeAttributeCode][] = $this->getTypeData($parameter['type']);
                    }
                }
            }
        }
        return $result;
    }

    public function generateType($typeName, $data)
    {
        if (!in_array($typeName, $this->generatedTypes)) {
            $this->generatedTypes[] = $typeName;
        } else {
            return;
        }

        $typeFields = $this->convertFieldsToGraphQlSchemaFormat($typeName, $data);
        if ($typeName == 'AbstractProduct') {
            echo <<<OUTPUT_ABSTRACT
 
interface {$typeName} {{$typeFields}
}\n\n
OUTPUT_ABSTRACT;
            echo <<<OUTPUT_SIMPLE
 
type SimpleProduct implements AbstractProduct {{$typeFields}
}\n\n
OUTPUT_SIMPLE;
            echo <<<OUTPUT_CONFIGURABLE
 
type ConfigurableProduct implements AbstractProduct {{$typeFields}
    configurable_product_links: [SimpleProduct]
    configurable_product_options: [ConfigurableProductOptions]
}

\n\n
OUTPUT_CONFIGURABLE;
        } else {
            echo <<<OUTPUT_TYPE
type {$typeName} {{$typeFields}
}\n\n
OUTPUT_TYPE;
        }
    }

    /**
     * Checks if array is associative
     *
     * @param $arr
     * @return bool
     */
    public function isAssociativeArray($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Transforms string from underscore to camelcase
     *
     * @param string $value
     * @return string
     */
    public function underscoreToCamelCase($value)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $value))));
    }

    /**
     * Converts array to graphQL schema
     *
     * @param $typeName
     * @param array $schema
     * @param bool $skipField
     * @param null $parentField
     * @return string
     */
    public function convertFieldsToGraphQlSchemaFormat(
        $typeName,
        array $schema,
        $skipField = false,
        $parentField = null
    ) {
        $productFields = '';

        foreach ($schema as $field => $type) {
            if (!$skipField) {
                $productFields .= "\n    {$field}: ";
            }

            if (is_array($type)) {
                $isAssociativeArray = $this->isAssociativeArray($type);
                if ($isAssociativeArray) {
                    $parentField = ucfirst($this->underscoreToCamelCase($parentField));
                    if ($field === 'content' || $field === 'video_content') {
                        $parentField = ucfirst($this->underscoreToCamelCase($field));
                        $this->generateType($typeName . $parentField, $type);
                        $productFields .= $typeName . $parentField;
                    } else {
                        $this->generateType($typeName . $parentField, $type);
                        $productFields .= $typeName . $parentField;
                    }
                } else {
                    $convertedField = $this->convertFieldsToGraphQlSchemaFormat(
                        $typeName,
                        $type,
                        !$isAssociativeArray,
                        $field
                    );
                    $productFields .= "[{$convertedField}]";
                }
            } else {
                $type = ucfirst($type);
                $productFields .= "{$type}";
            }
        }
        return $productFields;
    }

    /**
     * Reads graphQL schema
     *
     * @return string
     */
    private function readSchema()
    {
        /** @var \Magento\Webapi\Model\ServiceMetadata $serviceMetadata */
        $serviceMetadata = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Webapi\Model\ServiceMetadata::class
        );
        $serviceMetadata->getServicesConfig();
        /** @var \Magento\Eav\Api\AttributeManagementInterface $management */
        $management = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Eav\Api\AttributeManagementInterface::class
        );
        $result = [];
        $attributes = $management->getAttributes('catalog_product', 4);
        foreach ($attributes as $attribute) {
            $result[$attribute->getAttributeCode()] = 'string';
        }

        $staticAttributes = $this->getTypeData('CatalogDataProductInterface');
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

        ob_start();

        echo <<<OUTPUT
schema {
    query: Query
}

type Query {
    product(sku: String!): AbstractProduct
}\n\n
OUTPUT;
        $this->generateType('ConfigurableProductOptions', $result['configurable_product_options'][0]);
        unset($result['configurable_product_options']);
        $this->generateType('AbstractProduct', $result);

        $schema = ob_get_contents();
        ob_end_clean();
        return $schema;
    }
}
