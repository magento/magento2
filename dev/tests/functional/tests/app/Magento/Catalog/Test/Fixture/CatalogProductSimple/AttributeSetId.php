<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AttributeSetId
 *
 *  Data keys:
 *  - dataSet
 *  - attribute_set
 */
class AttributeSetId implements FixtureInterface
{
    /**
     * Attribute Set name
     *
     * @var string
     */
    protected $data;

    /**
     * Attribute Set fixture
     *
     * @var CatalogAttributeSet
     */
    protected $attributeSet;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;
        if (isset($data['dataSet']) && $data['dataSet'] !== '-') {
            $attributeSet = $fixtureFactory->createByCode('catalogAttributeSet', ['dataSet' => $data['dataSet']]);
        }
        if (isset($data['attribute_set']) && $data['attribute_set'] instanceof CatalogAttributeSet) {
            $attributeSet = $data['attribute_set'];
        }
        /** @var CatalogAttributeSet $attributeSet */
        if (!isset($data['dataSet']) && !isset($data['attribute_set'])) {
            $this->data = $data;
        } else {
            $this->data = $attributeSet->getAttributeSetName();
            $this->attributeSet = $attributeSet;
        }
    }

    /**
     * Persist attribute options
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param string|null $key
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return Attribute Set fixture
     *
     * @return CatalogAttributeSet
     */
    public function getAttributeSet()
    {
        return $this->attributeSet;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }
}
