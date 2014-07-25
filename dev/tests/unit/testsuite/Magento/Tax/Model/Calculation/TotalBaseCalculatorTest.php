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

namespace Magento\Tax\Model\Calculation;


use Magento\Tax\Model\Calculation;
use Magento\TestFramework\Helper\ObjectManager;
use Magento\Tax\Service\V1\Data\QuoteDetails;

/**
 * Class TotalBaseCalculatorTest
 *
 */
class TotalBaseCalculatorTest extends RowBaseAndTotalBaseCalculatorTestCase
{
    /** @var TotalBaseCalculator | \PHPUnit_Framework_MockObject_MockObject */
    protected $totalBaseCalculator;

    public function testCalculateWithTaxInPrice()
    {
        $this->initTotalBaseCalculator();
        $this->totalBaseCalculator->expects($this->exactly(3))
            ->method('deltaRound')->will($this->returnValue(0));
        $this->initMocks(true);

        $this->assertSame(
            self::EXPECTED_VALUE,
            $this->calculate($this->totalBaseCalculator)
        );
    }

    public function testCalculateWithTaxNotInPrice()
    {
        $this->initTotalBaseCalculator();
        $this->totalBaseCalculator->expects($this->exactly(2))
            ->method('deltaRound')->will($this->returnValue(0));
        $this->initMocks(false);

        $this->assertSame(
            self::EXPECTED_VALUE,
            $this->calculate($this->totalBaseCalculator)
        );
    }

    private function initTotalBaseCalculator()
    {
        $taxClassService = $this->objectManager->getObject('Magento\Tax\Service\V1\TaxClassService');
        $this->totalBaseCalculator = $this->getMockBuilder('Magento\Tax\Model\Calculation\TotalBaseCalculator')
            ->setConstructorArgs(
                [
                    'taxClassService' => $taxClassService,
                    'taxDetailsItemBuilder' => $this->mockTaxItemDetailsBuilder,
                    'calculationTool' => $this->mockCalculationTool,
                    'config' => $this->mockConfig,
                    'storeId' => self::STORE_ID,
                    'addressRateRequest' => $this->addressRateRequest
                ]
            )->setMethods(['deltaRound'])->getMock();
    }
}
