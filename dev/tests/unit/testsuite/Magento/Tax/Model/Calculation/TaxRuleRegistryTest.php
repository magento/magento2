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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Test for TaxRuleRegistry
 *
 */
class TaxRuleRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\Calculation\TaxRuleRegistry
     */
    private $taxRuleRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\Calculation\RuleFactory
     */
    private $taxRuleModelFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\Calculation\Rule
     */
    private $taxRuleModelMock;

    const TAX_RULE_ID = 1;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->taxRuleModelFactoryMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\RuleFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxRuleRegistry = $objectManager->getObject(
            'Magento\Tax\Model\Calculation\TaxRuleRegistry',
            ['taxRuleModelFactory' => $this->taxRuleModelFactoryMock]
        );
        $this->taxRuleModelMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\Rule')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testRemoveTaxRule()
    {
        $this->taxRuleModelMock->expects($this->any())
            ->method('load')
            ->with(self::TAX_RULE_ID)
            ->will($this->returnValue($this->taxRuleModelMock));

        $this->taxRuleModelMock->expects($this->any())
            ->method('getId')
            ->will($this->onConsecutiveCalls(self::TAX_RULE_ID, null));

        $this->taxRuleModelFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->taxRuleModelMock));
        $this->taxRuleRegistry->registerTaxRule($this->taxRuleModelMock);
        $expected = $this->taxRuleRegistry->retrieveTaxRule(self::TAX_RULE_ID);
        $this->assertEquals($this->taxRuleModelMock, $expected);

        // Remove the tax rule
        $this->taxRuleRegistry->removeTaxRule(self::TAX_RULE_ID);

        // Verify that if the tax rule is retrieved again, an exception is thrown
        try {
            $this->taxRuleRegistry->retrieveTaxRule(self::TAX_RULE_ID);
            $this->fail('NoSuchEntityException was not thrown as expected');
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'taxRuleId',
                'fieldValue' => self::TAX_RULE_ID,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        }
    }
}
