<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filter\Template;
use Magento\TestModuleSimpleTemplateDirective\Model\LegacyFilter;
use PHPUnit\Framework\TestCase;

class LegacyDirectiveTest extends TestCase
{
    /**
     * @var DependDirective
     */
    private $processor;

    /**
     * @var Template
     */
    private $filter;

    protected function setUp(): void
    {
        $objectManager = ObjectManager::getInstance();
        $this->filter = $objectManager->create(LegacyFilter::class);
        $this->processor = $objectManager->create(LegacyDirective::class);
    }

    public function testFallbackWithNoVariables()
    {
        $template = 'blah {{unknown foobar}} blah';
        $result = $this->processor->process($this->createConstruction($this->processor, $template), $this->filter, []);
        self::assertEquals('{{unknown foobar}}', $result);
    }

    /**
     * @dataProvider useCaseProvider
     */
    public function testCases(string $template, array $variables, string $expect)
    {
        $result = $this->processor->process(
            $this->createConstruction($this->processor, $template),
            $this->filter,
            $variables
        );
        self::assertEquals($expect, $result);
    }

    public static function useCaseProvider()
    {
        return [
            'protected method' => ['{{cool "blah" foo bar baz=bash}}', [], 'value1: cool: "blah" foo bar baz=bash'],
            'public method' => ['{{cooler "blah" foo bar baz=bash}}', [], 'value2: cooler: "blah" foo bar baz=bash'],
            'simple directive' => ['{{mydir "blah" param1=bash}}foo{{/mydir}}', [], 'OOFHSABHALB'],
        ];
    }

    private function createConstruction(LegacyDirective $directive, string $value): array
    {
        preg_match($directive->getRegularExpression(), $value, $construction);

        return $construction;
    }
}
