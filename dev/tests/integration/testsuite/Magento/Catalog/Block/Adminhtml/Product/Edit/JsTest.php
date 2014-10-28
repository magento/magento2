<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            ->setTaxCustomerClass(array_merge($fixtureTaxRule->getCustomerTaxClasses(), [$defaultCustomerTaxClass]))
            ->setTaxProductClass($fixtureTaxRule->getProductTaxClasses())
            ->setTaxRate($fixtureTaxRule->getRates())
            ->saveCalculationData();
        /** @var \Magento\Catalog\Block\Adminhtml\Product\Edit\Js $block */
        $block = $objectManager->create('Magento\Catalog\Block\Adminhtml\Product\Edit\Js');
        $jsonResult = $block->getAllRatesByProductClassJson();
        $decodedResult = json_decode($jsonResult);
        $this->assertNotEmpty($decodedResult, 'Resulting JSON is invalid.');
        $taxClassesArray = (array)$decodedResult;
        $defaultProductTaxClass = 2;
        $expectedProductTaxClasses = array_unique(
            array_merge($fixtureTaxRule->getProductTaxClasses(), [$defaultProductTaxClass])
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
