<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogAttributeSet;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Assigned attributes sources.
 *
 *  Data keys:
 *  - dataset
 *  - attributes
 */
class AssignedAttributes extends DataSource
{
    /**
     * Assigned attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataset']) && is_string($data['dataset'])) {
            $datasets = explode(',', $data['dataset']);
            foreach ($datasets as $dataset) {
                /** @var CatalogProductAttribute $attribute */
                $attribute = $fixtureFactory->createByCode('catalogProductAttribute', ['dataset' => $dataset]);
                $attribute->persist();

                $this->data[] = $attribute->getAttributeCode();
                $this->attributes[] = $attribute;
            }
        } elseif (isset($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attribute) {
                /** @var CatalogProductAttribute $attribute */
                $this->data[] = $attribute->getAttributeCode();
                $this->attributes[] = $attribute;
            }
        } else {
            $this->data = $data;
        }
    }

    /**
     * Get Attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
