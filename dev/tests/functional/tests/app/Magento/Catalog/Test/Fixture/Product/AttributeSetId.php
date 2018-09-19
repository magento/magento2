<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Product;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;

/**
 * Attribute set entity data source.
 *
 *  Data keys:
 *  - dataset
 *  - attribute_set
 */
class AttributeSetId extends DataSource
{
    /**
     * Attribute Set fixture.
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
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;
        if (isset($data['dataset']) && $data['dataset'] !== 'Default') {
            /** @var CatalogAttributeSet $attributeSet */
            $attributeSet = $fixtureFactory->createByCode('catalogAttributeSet', ['dataset' => $data['dataset']]);
            if (!$attributeSet->hasData('attribute_set_id')) {
                $attributeSet->persist();
            }
        }
        if (isset($data['attribute_set']) && $data['attribute_set'] instanceof CatalogAttributeSet) {
            $attributeSet = $data['attribute_set'];
        }
        if (!isset($data['dataset']) && !isset($data['attribute_set'])) {
            $this->data = $data;
        } else {
            /** @var CatalogAttributeSet $attributeSet */
            $this->data = $attributeSet->getAttributeSetName();
            $this->attributeSet = $attributeSet;
        }
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
