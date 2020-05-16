<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Api\Data\ValidationRuleInterface;
use Magento\Customer\Ui\Component\Listing\Column\ValidationRules;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidationRulesTest extends TestCase
{
    /** @var ValidationRules */
    protected $validationRules;

    /** @var ValidationRuleInterface|MockObject */
    protected $validationRule;

    protected function setUp(): void
    {
        $this->validationRule = $this->getMockBuilder(ValidationRuleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->validationRules = new ValidationRules();
    }

    /**
     * Tests input validation rules
     *
     * @param String $validationRule - provided input validation rules
     * @param String $validationClass - expected input validation class
     * @dataProvider validationRulesDataProvider
     */
    public function testGetValidationRules(String $validationRule, String $validationClass): void
    {
        $expectsRules = [
            'required-entry' => true,
            $validationClass => true,
        ];
        $this->validationRule->method('getName')
            ->willReturn('input_validation');

        $this->validationRule->method('getValue')
            ->willReturn($validationRule);

        self::assertEquals(
            $expectsRules,
            $this->validationRules->getValidationRules(
                true,
                [
                    $this->validationRule,
                    new DataObject(),
                ]
            )
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
            ['email', 'validate-email']
        ];
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
}
