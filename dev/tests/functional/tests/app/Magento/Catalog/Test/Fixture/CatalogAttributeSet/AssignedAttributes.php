<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogAttributeSet;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssignedAttributes
 *
 *  Data keys:
 *  - presets
 *  - attributes
 */
class AssignedAttributes implements FixtureInterface
{
    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params = [];

    /**
     * Names of assigned attributes
     *
     * @var array
     */
    protected $data = [];

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
        if (isset($data['presets']) && is_string($data['presets'])) {
            $presets = explode(',', $data['presets']);
            foreach ($presets as $preset) {
                /** @var CatalogProductAttribute $attribute */
                $attribute = $fixtureFactory->createByCode('catalogProductAttribute', ['dataSet' => $preset]);
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
     * Persist attribute
     *
     * @return void
     */
    public function persist()
    {
        //
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

    /**
     * Return prepared data set
     *
     * @param string|null $key
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
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
