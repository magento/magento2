<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Fixture for Attribute Sets and Attributes based on the configuration
 *
 * Support the following format:
 * <!-- Number of product attribute sets -->
 * <product_attribute_sets>{int}</product_attribute_sets>
 *
 * <!-- Number of attributes per set -->
 * <product_attribute_sets_attributes>{int}</product_attribute_sets_attributes>
 *
 * <!-- Number of values per attribute -->
 * <product_attribute_sets_attributes_values>{int}</product_attribute_sets_attributes_values>
 *
 * @see setup/performance-toolkit/profiles/ce/small.xml
 * @since 2.2.0
 */
class AttributeSetsFixture extends Fixture
{
    /** Name of generated attribute set */
    const PRODUCT_SET_NAME = 'Product Set ';

    /**
     * @var int
     * @since 2.2.0
     */
    protected $priority = 25;

    /**
     * @var AttributeSet\AttributeSetFixture
     * @since 2.2.0
     */
    private $attributeSetsFixture;

    /**
     * @var AttributeSet\Pattern
     * @since 2.2.0
     */
    private $pattern;

    /**
     * @param FixtureModel $fixtureModel
     * @param AttributeSet\AttributeSetFixture $attributeSetsFixture
     * @param AttributeSet\Pattern $pattern
     * @since 2.2.0
     */
    public function __construct(
        FixtureModel $fixtureModel,
        AttributeSet\AttributeSetFixture $attributeSetsFixture,
        \Magento\Setup\Fixtures\AttributeSet\Pattern $pattern
    ) {
        parent::__construct($fixtureModel);
        $this->attributeSetsFixture = $attributeSetsFixture;
        $this->pattern = $pattern;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function execute()
    {
        $attributeSets = $this->fixtureModel->getValue('attribute_sets', null);
        if ($attributeSets !== null) {
            foreach ($attributeSets['attribute_set'] as $attributeSetData) {
                $this->attributeSetsFixture->createAttributeSet($attributeSetData);
            }
        }

        $attributeSetsCount = $this->fixtureModel->getValue('product_attribute_sets', null);
        if ($attributeSetsCount !== null) {
            for ($index = 1; $index <= $attributeSetsCount; $index++) {
                $this->attributeSetsFixture->createAttributeSet(
                    $this->pattern->generateAttributeSet(
                        self::PRODUCT_SET_NAME . $index,
                        $this->fixtureModel->getValue('product_attribute_sets_attributes', 3),
                        $this->fixtureModel->getValue('product_attribute_sets_attributes_values', 3),
                        function ($attributeIndex, $attribute) use ($index) {
                            return array_replace_recursive(
                                $attribute,
                                [
                                    'attribute_code' => "attribute_set{$index}_" . $attributeIndex,
                                ]
                            );
                        }
                    )
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getActionTitle()
    {
        return 'Generating attribute sets';
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function introduceParamLabels()
    {
        return [
            'attribute_sets' => 'Attribute Sets (Default)',
            'product_attribute_sets' => 'Attribute Sets (Extra)'
        ];
    }
}
