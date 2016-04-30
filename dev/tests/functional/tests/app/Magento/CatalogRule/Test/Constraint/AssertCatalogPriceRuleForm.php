<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCatalogPriceRuleForm
 */
class AssertCatalogPriceRuleForm extends AbstractConstraint
{
    /**
     * Assert that displayed Catalog Price Rule data on edit page equals passed from fixture.
     *
     * @param CatalogRule $catalogPriceRule
     * @param CatalogRuleIndex $pageCatalogRuleIndex
     * @param CatalogRuleNew $pageCatalogRuleNew
     * @param CatalogRule $catalogPriceRuleOriginal
     * @return void
     */
    public function processAssert(
        CatalogRule $catalogPriceRule,
        CatalogRuleIndex $pageCatalogRuleIndex,
        CatalogRuleNew $pageCatalogRuleNew,
        CatalogRule $catalogPriceRuleOriginal = null
    ) {
        $data = ($catalogPriceRuleOriginal === null)
            ? $catalogPriceRule->getData()
            : array_merge($catalogPriceRuleOriginal->getData(), $catalogPriceRule->getData());
        $filter['name'] = $data['name'];

        $pageCatalogRuleIndex->open();
        $pageCatalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
        $formData = $pageCatalogRuleNew->getEditForm()->getData($catalogPriceRule);
        $fixtureData = $catalogPriceRule->getData();
        //convert discount_amount to float to compare
        if (isset($formData['discount_amount'])) {
            $formData['discount_amount'] = floatval($formData['discount_amount']);
        }
        if (isset($fixtureData['discount_amount'])) {
            $fixtureData['discount_amount'] = floatval($fixtureData['discount_amount']);
        }
        $diff = $this->verifyData($formData, $fixtureData);
        \PHPUnit_Framework_Assert::assertTrue(
            empty($diff),
            implode(' ', $diff)
        );
    }

    /**
     * Check if arrays have equal values
     *
     * @param array $formData
     * @param array $fixtureData
     * @return array
     */
    protected function verifyData(array $formData, array $fixtureData)
    {
        $errorMessage = [];
        foreach ($fixtureData as $key => $value) {
            if ($key == 'conditions') {
                continue;
            }
            if (is_array($value)) {
                $diff = array_diff($value, $formData[$key]);
                $diff = array_merge($diff, array_diff($formData[$key], $value));
                if (!empty($diff)) {
                    $errorMessage[] = "Data in " . $key . " field not equal."
                        . "\nExpected: " . implode(", ", $value)
                        . "\nActual: " . implode(", ", $formData[$key]);
                }
            } else {
                if ($value !== $formData[$key]) {
                    $errorMessage[] = "Data in " . $key . " field not equal."
                        . "\nExpected: " . $value
                        . "\nActual: " . $formData[$key];
                }
            }
        }
        return $errorMessage;
    }

    /**
     * Text success verify Catalog Price Rule
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed catalog price rule data on edit page(backend) equals to passed from fixture.';
    }
}
