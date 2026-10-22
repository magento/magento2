<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\ConfigurableProduct\Model;

use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Framework\DB\Select;

/**
 * Provider for retrieving configurable options.
 */
class AttributeOptionProvider implements AttributeOptionProviderInterface
{
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Attribute
     */
    private $attributeResource;

    /**
     * @var OptionSelectBuilderInterface
     */
    private $optionSelectBuilder;

    /**
     * @param Attribute $attributeResource
     * @param ScopeResolverInterface $scopeResolver
     * @param OptionSelectBuilderInterface $optionSelectBuilder
     */
    public function __construct(
        Attribute $attributeResource,
        ScopeResolverInterface $scopeResolver,
        OptionSelectBuilderInterface $optionSelectBuilder
    ) {
        $this->attributeResource = $attributeResource;
        $this->scopeResolver = $scopeResolver;
        $this->optionSelectBuilder = $optionSelectBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeOptions(AbstractAttribute $superAttribute, $productId)
    {
        $scope  = $this->scopeResolver->getScope();
        $select = $this->optionSelectBuilder->getSelect($superAttribute, $productId, $scope);
        $data = $this->attributeResource->getConnection()->fetchAll($select);

        if ($superAttribute->getSourceModel()) {
            $optionLabels = $this->getOptionLabels($superAttribute, $this->getValueIndexes($data));

            foreach ($data as $key => $value) {
                $valueIndex = $value['value_index'] ?? null;
                $optionText = ($valueIndex !== null && isset($optionLabels[$valueIndex]))
                    ? $optionLabels[$valueIndex]
                    : false;
                $data[$key]['default_title'] = $optionText;
                $data[$key]['option_title'] = $optionText;
            }
        }

        return $data;
    }

    /**
     * Collect distinct option value indexes present in the fetched rows
     *
     * @param array $data
     * @return array
     */
    private function getValueIndexes(array $data): array
    {
        $valueIndexes = [];
        foreach ($data as $row) {
            $valueIndex = $row['value_index'] ?? null;
            if ($valueIndex !== null) {
                $valueIndexes[$valueIndex] = $valueIndex;
            }
        }

        return array_values($valueIndexes);
    }

    /**
     * Get option labels indexed by option value
     *
     * Only the options used by the product are loaded when the source supports it, since loading every option of
     * the attribute is prohibitively slow for attributes with a large number of options.
     *
     * @param AbstractAttribute $superAttribute
     * @param array $valueIndexes
     * @return array
     */
    private function getOptionLabels(AbstractAttribute $superAttribute, array $valueIndexes): array
    {
        if (!$valueIndexes) {
            return [];
        }

        $source = $superAttribute->getSource();
        // getSpecificOptions() is not a part of the source contract, so custom source models may not implement it
        $options = method_exists($source, 'getSpecificOptions')
            ? $source->getSpecificOptions($valueIndexes, false)
            : $source->getAllOptions(false);

        $optionLabels = [];
        foreach ($options as $option) {
            $optionValue = $option['value'] ?? null;
            if ($optionValue !== null) {
                $optionLabels[$optionValue] = $option['label'];
            }
        }

        return $optionLabels;
    }
}
