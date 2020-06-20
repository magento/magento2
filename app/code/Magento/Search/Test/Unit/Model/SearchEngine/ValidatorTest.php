<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model\SearchEngine;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\SearchEngine\Validator;
use Magento\Search\Model\SearchEngine\ValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test search engine validator
 */
class ValidatorTest extends TestCase
{
    private $validator;

    private $otherEngineValidatorMock;

    private $scopeConfigMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->otherEngineValidatorMock = $this->getMockForAbstractClass(ValidatorInterface::class);
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
        $this->validator = $objectManager->getObject(
            Validator::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'engineValidators' => ['otherEngine' => $this->otherEngineValidatorMock],
                'excludedEngineList' => ['badEngine' => 'Bad Engine']
            ]
        );
    }

    public function testValidateValid()
    {
        $expectedErrors = [];

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/search/engine')
            ->willReturn('otherEngine');

        $this->otherEngineValidatorMock->expects($this->once())->method('validate')->willReturn([]);

        $this->assertEquals($expectedErrors, $this->validator->validate());
    }

    public function testValidateExcludedList()
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/search/engine')
            ->willReturn('badEngine');

        $expectedErrors = [
            "Your current search engine, 'Bad Engine', is not supported."
            . " You must install a supported search engine before upgrading."
            . " See the System Upgrade Guide for more information."
        ];

        $this->assertEquals($expectedErrors, $this->validator->validate());
    }

    public function testValidateInvalid()
    {
        $expectedErrors = ['Validation failed for otherEngine'];

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/search/engine')
            ->willReturn('otherEngine');
        $this->otherEngineValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn($expectedErrors);

        $this->assertEquals($expectedErrors, $this->validator->validate());
    }
}
