<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Ui\Component\Listing\Column\ValidationRules;
use Magento\Customer\Api\Data\ValidationRuleInterface;

class ValidationRulesTest extends \PHPUnit\Framework\TestCase
{
    /** @var ValidationRules */
    protected $validationRules;

    /** @var ValidationRuleInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $validationRule;

    protected function setUp()
    {
        $this->validationRule = $this->getMockBuilder(\Magento\Customer\Api\Data\ValidationRuleInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validationRules = new ValidationRules();
    }

    /**
     * Tests input validation rules.
     *
     * @param string $validationRule
     * @param string $validationClass
     * @return void
     * @dataProvider validationRulesDataProvider
     */
    public function testGetValidationRules(string $validationRule, string $validationClass)
    {
        $expectsRules = [
            'required-entry' => true,
            $validationClass => true,
        ];
        $this->validationRule->method('getName')
            ->willReturn('input_validation');

        $this->validationRule->method('getValue')
            ->willReturn($validationRule);

        $this->assertEquals(
            $expectsRules,
            $this->validationRules->getValidationRules(
                true,
                [
                    $this->validationRule,
                    new \Magento\Framework\DataObject(),
                ]
            )
        );
    }

    public function testGetValidationRulesWithOnlyRequiredRule()
    {
        $expectsRules = [
            'required-entry' => true,
        ];
        $this->assertEquals(
            $expectsRules,
            $this->validationRules->getValidationRules(true, [])
        );
    }

    /**
     * Provides possible validation rules.
     *
     * @return array
     */
    public function validationRulesDataProvider(): array
    {
        return [
            ['alpha', 'validate-alpha'],
            ['numeric', 'validate-number'],
            ['alphanumeric', 'validate-alphanum'],
            ['alphanum-with-spaces', 'validate-alphanum-with-spaces'],
            ['url', 'validate-url'],
            ['email', 'validate-email'],
        ];
    }
}
