<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductPage;
use Magento\Catalog\Test\TestStep\OpenProductOnFrontendStep;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Assert that swatch attributes are displayed on product page
 */
class AssertSwatchOptionsOnProductPage extends AbstractConstraint
{
    /**
     * @param TestStepFactory $stepFactory
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     */
    public function processAssert(
        TestStepFactory $stepFactory,
        CatalogProductView $catalogProductView,
        FixtureInterface $product
    ) {
        $stepFactory->create(OpenProductOnFrontendStep::class, ['product' => $product])
            ->run();

        $actualData = $catalogProductView->getProductViewWithSwatchesBlock()
            ->getSwatchAttributesData();
        $expectedData = $product->getConfigurableAttributesData()['attributes_data'];

        foreach ($expectedData as $expectedAttributeData) {
            \PHPUnit_Framework_Assert::assertArrayHasKey(
                $expectedAttributeData['attribute_code'],
                $actualData,
                'Attribute with code ' . $expectedAttributeData['attribute_code'] . ' is absent on Product page'
            );
            $actualAttributeData = $actualData[$expectedAttributeData['attribute_code']];
            $this->verifyAttribute($expectedAttributeData, $actualAttributeData);
            $this->verifyAttributeOptions($expectedAttributeData, $actualAttributeData);
        }
    }

    /**
     * Verify attribute data
     *
     * @param array $expectedAttributeData
     * @param array $actualAttributeData
     */
    private function verifyAttribute(array $expectedAttributeData, array $actualAttributeData)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedAttributeData['attribute_code'],
            $actualAttributeData['attribute_code'],
            sprintf(
                'Attribute code "%s" is not equal to expected "%s"',
                $actualAttributeData['attribute_code'],
                $expectedAttributeData['attribute_code']
            )
        );
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedAttributeData['attribute_id'],
            $actualAttributeData['attribute_id'],
            sprintf(
                'Attribute id "%s" is not equal to expected "%s"',
                $actualAttributeData['attribute_id'],
                $expectedAttributeData['attribute_id']
            )
        );
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedAttributeData['label'],
            $actualAttributeData['label'],
            sprintf(
                'Attribute label "%s" is not equal to expected "%s"',
                $actualAttributeData['label'],
                $expectedAttributeData['label']
            )
        );
    }

    /**
     * Verify attribute options data
     *
     * @param array $expectedAttributeData
     * @param array $actualAttributeData
     */
    private function verifyAttributeOptions(array $expectedAttributeData, array $actualAttributeData)
    {
        if (isset($expectedAttributeData['options'])) {
            \PHPUnit_Framework_Assert::assertArrayHasKey(
                'options',
                $actualAttributeData,
                'Swatch attribute options are missed on Product page'
            );

            $expectedOptionsCount = count($expectedAttributeData['options']);
            $actualOptionsCount = count($actualAttributeData['options']);
            \PHPUnit_Framework_Assert::assertEquals(
                $expectedOptionsCount,
                $actualOptionsCount,
                sprintf(
                    'Attribute options count "%d" is not equal to expected "%d"',
                    $actualOptionsCount,
                    $expectedOptionsCount
                )
            );
        } else {
            \PHPUnit_Framework_Assert::assertArrayNotHasKey(
                'options',
                $actualAttributeData,
                'Product page must be without swatch attribute options'
            );
        }
    }

    /**
     * Return string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Swatch attributes are displayed on product page';
    }
}
