<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogAttributeSet;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;

/**
 * Class SkeletonSet
 *
 *  Data keys:
 *  - dataset
 */
class SkeletonSet extends DataSource
{
    /**
     * New Attribute Set
     *
     * @var array
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
        if (isset($data['dataset']) && $data['dataset'] !== '-') {
            $parentSet = $fixtureFactory->createByCode('catalogAttributeSet', ['dataset' => $data['dataset']]);
            if (!$parentSet->hasData('attribute_set_id')) {
                $parentSet->persist();
            }
            /** @var CatalogAttributeSet $parentSet */
            $this->data = $parentSet->getAttributeSetName();
            $this->attributeSet = $parentSet;
        }
    }

    /**
     * Get Attribute Set
     *
     * @return array
     */
    public function getAttributeSet()
    {
        return $this->attributeSet;
    }
}
