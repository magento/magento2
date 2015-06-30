<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Test\Fixture\GoogleShoppingAttribute;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;

/**
 * Prepare Attribute Set
 *
 *  Data keys:
 *  - dataset
 *  - attribute_set
 */
class AttributeSetId extends DataSource
{
    /**
     * Attribute Set fixture
     *
     * @var CatalogAttributeSet
     */
    protected $attributeSet;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataset'])) {
            /** @var CatalogAttributeSet $attributeSet */
            $attributeSet = $fixtureFactory->createByCode('catalogAttributeSet', ['dataset' => $data['dataset']]);
            $this->prepareData($attributeSet);
        }

        if (isset($data['attribute_set']) && $data['attribute_set'] instanceof CatalogAttributeSet) {
            $this->prepareData($data['attribute_set']);
        }
    }

    /**
     * Prepare Catalog Attribute Set data.
     *
     * @param CatalogAttributeSet $attributeSet
     * @return void
     */
    protected function prepareData(CatalogAttributeSet $attributeSet)
    {
        if (!$attributeSet->hasData('attribute_set_id')) {
            $attributeSet->persist();
        }

        $this->data = $attributeSet->getAttributeSetName();
        $this->attributeSet = $attributeSet;
    }

    /**
     * Return Attribute Set fixture.
     *
     * @return CatalogAttributeSet
     */
    public function getAttributeSet()
    {
        return $this->attributeSet;
    }
}
