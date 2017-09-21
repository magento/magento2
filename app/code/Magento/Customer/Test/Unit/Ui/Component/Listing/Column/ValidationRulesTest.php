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
        $this->validationRules = $this->getMockBuilder(
            \Magento\Customer\Ui\Component\Listing\Column\ValidationRules::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->validationRule = $this->getMockBuilder(\Magento\Customer\Api\Data\ValidationRuleInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validationRules = new ValidationRules();
    }

    public function testGetValidationRules()
    {
        $expectsRules = [
            'required-entry' => true,
            'validate-number' => true,
        ];
        $this->validationRule->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('input_validation');
        $this->validationRule->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('numeric');

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
}
