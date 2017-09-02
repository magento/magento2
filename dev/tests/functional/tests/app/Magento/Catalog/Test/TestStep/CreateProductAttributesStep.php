<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create product attributes step.
 */
class CreateProductAttributesStep implements TestStepInterface
{
    /**
     * Factory for Fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Factory for creation Test Step.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * Product attributes in data set.
     *
     * @var string
     */
    private $attributes;

    /**
     * @var CatalogProductAttribute[]
     */
    private $attributesForCleanUp;

    /**
     * CreateProductAttributesStep constructor.
     * @param TestStepFactory $testStepFactory
     * @param FixtureFactory $fixtureFactory
     * @param string|array $attribute
     */
    public function __construct(
        TestStepFactory $testStepFactory,
        FixtureFactory $fixtureFactory,
        $attribute
    ) {
        $this->testStepFactory = $testStepFactory;
        $this->attributes = $attribute;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Creates attributes.
     *
     * @return array
     */
    public function run()
    {
        $attributes = [];
        $attributeDataSets = is_array($this->attributes) ? $this->attributes : explode(',', $this->attributes);
        foreach ($attributeDataSets as $key => $attributeDataSet) {
            /** @var FixtureInterface[] $attributes */
            $attributes[$key] = $this->fixtureFactory->createByCode(
                'catalogProductAttribute',
                ['dataset' => trim($attributeDataSet)]
            );
            if ($attributes[$key]->hasData('id') === false) {
                $attributes[$key]->persist();
            }
        }
        $this->attributesForCleanUp = $attributes;

        return ['attribute' => $attributes];
    }

    /**
     * Deletes created attributes.
     *
     * @return void
     */
    public function cleanup()
    {
        if (is_array($this->attributesForCleanUp)) {
            foreach ($this->attributesForCleanUp as $attribute) {
                $this->testStepFactory->create(
                    DeleteAttributeStep::class,
                    ['attribute' => $attribute]
                )->run();
            }
        }
    }
}
