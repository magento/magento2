<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\ConfigurableProduct\Test\Handler\ConfigurableProductInjectable;

use Mtf\System\Config;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\Protocol\CurlTransport;
use Magento\Catalog\Test\Handler\CatalogProductSimple\Curl as ProductCurl;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable\ConfigurableAttributesData;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Class Curl
 * Create new configurable product via curl
 */
class Curl extends ProductCurl implements ConfigurableProductInjectableInterface
{
    /**
     * Constructor
     *
     * @param Config $configuration
     */
    public function __construct(Config $configuration)
    {
        parent::__construct($configuration);

        $this->mappingData += [
            'is_percent' => [
                'Yes' => 1,
                'No' => 0
            ],
            'include' => [
                'Yes' => 1,
                'No' => 0
            ]
        ];
    }

    /**
     * Prepare POST data for creating product request
     *
     * @param FixtureInterface $product
     * @param string|null $prefix [optional]
     * @return array
     */
    protected function prepareData(FixtureInterface $product, $prefix = null)
    {
        $data = parent::prepareData($product, null);

        /** @var ConfigurableAttributesData $configurableAttributesData */
        $configurableAttributesData = $product->getDataFieldConfig('configurable_attributes_data')['source'];
        $attributeSetId = $data['attribute_set_id'];

        $data['configurable_attributes_data'] = $this->prepareAttributesData($configurableAttributesData);
        $data = $prefix ? [$prefix => $data] : $data;
        $data['variations-matrix'] = $this->prepareVariationsMatrix($product);
        $data['attributes'] = $this->prepareAttributes($configurableAttributesData);
        $data['new-variations-attribute-set-id'] = $attributeSetId;
        $data['associated_product_ids'] = [];

        return $this->replaceMappingData($data);
    }

    /**
     * Preparing attribute data
     *
     * @param ConfigurableAttributesData $configurableAttributesData
     * @return array
     */
    protected function prepareAttributesData(ConfigurableAttributesData $configurableAttributesData)
    {
        $optionFields = [
            'pricing_value',
            'is_percent',
            'include',
        ];
        $result = [];

        foreach ($configurableAttributesData->getAttributesData() as $attribute) {
            $attributeId = isset($attribute['attribute_id']) ? $attribute['attribute_id'] : null;
            $dataOptions = [];

            foreach ($attribute['options'] as $option) {
                $optionId = isset($option['id']) ? $option['id'] : null;

                $dataOption = array_intersect_key($option, array_flip($optionFields));
                $dataOption['value_index'] = $optionId;

                $dataOptions[$optionId] = $dataOption;
            }

            $result[$attributeId] = [
                'code' => $attribute['attribute_code'],
                'attribute_id' => $attributeId,
                'label' => $attribute['frontend_label'],
                'values' => $dataOptions
            ];
        }

        return $result;
    }

    /**
     * Preparing matrix data
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareVariationsMatrix(FixtureInterface $product)
    {
        /** @var ConfigurableAttributesData $configurableAttributesData */
        $configurableAttributesData = $product->getDataFieldConfig('configurable_attributes_data')['source'];
        $attributesData = $configurableAttributesData->getAttributesData();
        $matrixData = $product->getConfigurableAttributesData()['matrix'];
        $result = [];

        foreach ($matrixData as $variationKey => $variation) {
            $compositeKeys = explode(' ', $variationKey);
            $keyIds = [];
            $configurableAttribute = [];

            foreach ($compositeKeys as $compositeKey) {
                list($attributeKey, $optionKey) = explode(':', $compositeKey);
                $attribute = $attributesData[$attributeKey];

                $keyIds[] = $attribute['options'][$optionKey]['id'];
                $configurableAttribute[] = sprintf(
                    '"%s":"%s"',
                    $attribute['attribute_code'],
                    $attribute['options'][$optionKey]['id']
                );
            }

            $keyIds = implode('-', $keyIds);
            $variation['configurable_attribute'] = '{' . implode(',', $configurableAttribute) . '}';
            $result[$keyIds] = $variation;
        }

        return $result;
    }

    /**
     * Prepare attributes
     *
     * @param ConfigurableAttributesData $configurableAttributesData
     * @return array
     */
    protected function prepareAttributes(ConfigurableAttributesData $configurableAttributesData)
    {
        $ids = [];

        foreach ($configurableAttributesData->getAttributes() as $attribute) {
            /** @var CatalogProductAttribute $attribute */
            $ids[] = $attribute->getAttributeId();
        }
        return $ids;
    }
}
