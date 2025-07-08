<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Model\Calculation\Rule;
use Magento\Tax\Model\Calculation\RuleFactory;
use Magento\Tax\Model\Calculation\TaxRuleRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for TaxRuleRegistry
 *
 */
class TaxRuleRegistryTest extends TestCase
{
    /**
     * @var TaxRuleRegistry
     */
    private $taxRuleRegistry;

    /**
     * @var MockObject|RuleFactory
     */
    private $taxRuleModelFactoryMock;

    /**
     * @var MockObject|Rule
     */
    private $taxRuleModelMock;

    const TAX_RULE_ID = 1;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->taxRuleModelFactoryMock = $this->getMockBuilder(RuleFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxRuleRegistry = $objectManager->getObject(
            TaxRuleRegistry::class,
            ['taxRuleModelFactory' => $this->taxRuleModelFactoryMock]
        );
        $this->taxRuleModelMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testRemoveTaxRule()
    {
        $this->taxRuleModelMock->expects($this->any())
            ->method('load')
            ->with(self::TAX_RULE_ID)
            ->willReturn($this->taxRuleModelMock);

        $this->taxRuleModelMock->expects($this->any())
            ->method('getId')
            ->will($this->onConsecutiveCalls(self::TAX_RULE_ID, null));

        $this->taxRuleModelFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->taxRuleModelMock);
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
