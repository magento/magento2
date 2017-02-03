<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Handler\ConfigurableProduct;

use Magento\Catalog\Test\Handler\CatalogProductSimple\Webapi as ProductWebapi;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct\ConfigurableAttributesData;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Create new configurable product via webapi.
 */
class Webapi extends ProductWebapi implements ConfigurableProductInterface
{
    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     * @param WebapiDecorator $webapiTransport
     * @param Curl $handlerCurl
     * @param FixtureFactory $fixtureFactory
     */
    public function __construct(
        DataInterface $configuration,
        EventManagerInterface $eventManager,
        WebapiDecorator $webapiTransport,
        Curl $handlerCurl,
        FixtureFactory $fixtureFactory
    ) {
        parent::__construct($configuration, $eventManager, $webapiTransport, $handlerCurl);
    }

    /**
     * Prepare data for creating product request.
     *
     * @return void
     */
    protected function prepareData()
    {
        parent::prepareData();
        $this->prepareConfigurableProductOptions();
        $this->prepareAttributeSet();
    }

    /**
     * Preparation of product options data.
     *
     * @return void
     */
    protected function prepareConfigurableProductOptions()
    {
        $configurableProductOptions = [];
        $configurableProductLinks = $this->getConfigurableProductLinks();

        if (isset($this->fields['product']['configurable_attributes_data'])) {
            $configurableAttributesData = $this->fields['product']['configurable_attributes_data'];

            foreach ($configurableAttributesData as $attributeId => $attributeData) {
                $attributeValues = [];
                foreach ($attributeData['values'] as $valueData) {
                    $attributeValues[] = [
                        'value_index' => $valueData['value_index']
                    ];
                }

                $configurableProductOptions[] = [
                    'attribute_id' => $attributeId,
                    'label' => $attributeData['label'],
                    'values' => $attributeValues
                ];
            }
        }

        $this->fields['product']['extension_attributes']['configurable_product_options'] = $configurableProductOptions;
        $this->fields['product']['extension_attributes']['configurable_product_links'] = $configurableProductLinks;
        unset($this->fields['product']['configurable_attributes_data']);
        unset($this->fields['attributes']);
        unset($this->fields['variations-matrix']);
        unset($this->fields['associated_product_ids']);
    }

    /**
     * Prepare and return links of associated products.
     *
     * @return array
     */
    protected function getConfigurableProductLinks()
    {
        if (!empty($this->fields['associated_product_ids'])) {
            return $this->fields['associated_product_ids'];
        }

        /** @var ConfigurableAttributesData $configurableAttributesData */
        $configurableAttributesData = $this->fixture->getDataFieldConfig('configurable_attributes_data')['source'];
        $associatedProductIds = [];

        $configurableAttributesData->generateProducts();
        foreach ($configurableAttributesData->getProducts() as $product) {
            $associatedProductIds[] = $product->getId();
            $this->fields['product']['attribute_set_id'] = $product->getDataFieldConfig('attribute_set_id')['source']
                ->getAttributeSet()->getAttributeSetId();
        }

        return $associatedProductIds;
    }

    /**
     * Preparation of attribute set data.
     *
     * @return void
     */
    protected function prepareAttributeSet()
    {
        /** @var ConfigurableAttributesData $configurableAttributesData */
        $configurableAttributesData = $this->fixture->getDataFieldConfig('configurable_attributes_data')['source'];
        $attributeSet = $configurableAttributesData->getAttributeSet();

        $this->fields['product']['attribute_set_id'] = $attributeSet->getAttributeSetId();
    }
}
