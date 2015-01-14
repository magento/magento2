<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Test\Fixture\GoogleShoppingAttribute;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AttributeSetId
 * Prepare Attribute Set
 *
 *  Data keys:
 *  - dataSet
 *  - attribute_set
 */
class AttributeSetId implements FixtureInterface
{
    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params = [];

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
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataSet'])) {
            /** @var CatalogAttributeSet $attributeSet */
            $attributeSet = $fixtureFactory->createByCode('catalogAttributeSet', ['dataSet' => $data['dataSet']]);
            $this->prepareData($attributeSet);
        }

        if (isset($data['attribute_set']) && $data['attribute_set'] instanceof CatalogAttributeSet) {
            $this->prepareData($data['attribute_set']);
        }
    }

    /**
     * Prepare Catalog Attribute Set data
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
