<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Edit;

/**
 * @magentoAppArea adminhtml
 */
class JsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testGetAllRatesByProductClassJson()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Tax\Model\Calculation\Rule $fixtureTaxRule */
        $fixtureTaxRule = $objectManager->create('Magento\Tax\Model\Calculation\Rule');
        $fixtureTaxRule->load('Test Rule', 'code');
        $defaultCustomerTaxClass = 3;
        $fixtureTaxRule
            ->setCustomerTaxClassIds(array_merge($fixtureTaxRule->getCustomerTaxClasses(), [$defaultCustomerTaxClass]))
            ->setProductTaxClassIds($fixtureTaxRule->getProductTaxClasses())
            ->setTaxRateIds($fixtureTaxRule->getRates())
            ->saveCalculationData();
        /** @var \Magento\Catalog\Block\Adminhtml\Product\Edit\Js $block */
        $block = $objectManager->create('Magento\Catalog\Block\Adminhtml\Product\Edit\Js');
        $jsonResult = $block->getAllRatesByProductClassJson();
        $decodedResult = json_decode($jsonResult);
        $this->assertNotEmpty($decodedResult, 'Resulting JSON is invalid.');
        $taxClassesArray = (array)$decodedResult;
        $noneTaxClass = 0;
        $defaultProductTaxClass = 2;
        $expectedProductTaxClasses = array_unique(
            array_merge($fixtureTaxRule->getProductTaxClasses(), [$defaultProductTaxClass, $noneTaxClass])
        );
        $this->assertCount(
            count($expectedProductTaxClasses),
            $taxClassesArray,
            'Invalid quantity of rates for tax classes.'
        );
        foreach ($expectedProductTaxClasses as $taxClassId) {
            $this->assertArrayHasKey(
                "value_{$taxClassId}",
                $taxClassesArray,
                "Rates for tax class with ID '{$taxClassId}' is missing."
            );
        }
        $this->assertContains('7.5', $jsonResult, 'Rates for tax classes looks to be invalid.');
    }
}
