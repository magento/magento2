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
namespace Magento\Tax\Model\Sales\Total\Quote;

/**
 * Test class for \Magento\Tax\Model\Sales\Total\Quote\Tax
 */
use Magento\Tax\Model\Calculation;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the specific method
     *
     * @param string $calculationSequence
     * @param string $keyExpected
     * @param string $keyAbsent
     * @dataProvider dataProviderProcessConfigArray
     */
    public function testProcessConfigArray($calculationSequence, $keyExpected, $keyAbsent)
    {
        $taxData = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);
        $taxData
            ->expects($this->any())
            ->method('getCalculationSequence')
            ->will($this->returnValue($calculationSequence));

        $taxConfig = $this->getMock('\Magento\Tax\Model\Config', [], [], '', false);
        $taxCalculationService = $this->getMock('\Magento\Tax\Service\V1\TaxCalculationService', [], [], '', false);
        $quoteDetailsBuilder = $this->getMock('\Magento\Tax\Service\V1\Data\QuoteDetailsBuilder', [], [], '', false);

        /** @var \Magento\Tax\Model\Sales\Total\Quote\Tax */
        $taxTotalsCalcModel = new Tax($taxConfig, $taxCalculationService, $quoteDetailsBuilder, $taxData);
        $array = $taxTotalsCalcModel->processConfigArray([], null);
        $this->assertArrayHasKey($keyExpected, $array, 'Did not find the expected array key: ' . $keyExpected);
        $this->assertArrayNotHasKey($keyAbsent, $array, 'Should not have found the array key; ' . $keyAbsent);
    }

    /**
     * @return array
     */
    public function dataProviderProcessConfigArray()
    {
        return [
            [Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL, 'before', 'after'],
            [Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL, 'after', 'before'],
            [Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL, 'after', 'before'],
            [Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL, 'after', 'before']
        ];
    }
}
