<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\VariableResolver\StrictResolver;
use Magento\Framework\Filter\VariableResolverInterface;
use PHPUnit\Framework\TestCase;

class VarDirectiveTest extends TestCase
{
    /**
     * @var VariableResolverInterface
     */
    private $variableResolver;

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
        $this->variableResolver = $objectManager->get(StrictResolver::class);
        $this->filter = $objectManager->get(Template::class);
        $this->processor = $objectManager->create(
            VarDirective::class,
            ['variableResolver' => $this->variableResolver]
        );
    }

    public function testFallback()
    {
        $template = 'blah {{var}} blah';
        $result = $this->processor->process($this->createConstruction($this->processor, $template), $this->filter, []);
        self::assertSame('{{var}}', $result);
    }

    /**
     * @dataProvider useCasesProvider
     */
    public function testCases(string $parameter, array $variables, string $expect)
    {
        $template = 'blah {{var ' . $parameter . '}} blah';
        $result = $this->processor->process(
            $this->createConstruction($this->processor, $template),
            $this->filter,
            $variables
        );
        self::assertEquals($expect, $result);
    }

    public function useCasesProvider()
    {
        return [
            ['foo',['foo' => true], '1'],
            ['foo',['foo' => 'abc'], 'abc'],
            ['foo',['foo' => 1.234], '1.234'],
            ['foo',['foo' => 0xF], '15'],
            ['foo',['foo' => false], ''],
            ['foo',['foo' => null], ''],
            ['foo.bar',['foo' => ['bar' => 'abc']], 'abc'],
            ['foo.bar',['foo' => ['bar' => false]], ''],
            ['foo.getBar().baz',['foo' => new DataObject(['bar' => ['baz' => 'abc']])], 'abc'],
            ['foo.getBar().baz',['foo' => new DataObject(['bar' => ['baz' => false]])], ''],
            [
                'foo.getBar().baz|foofilter|nl2br',
                ['foo' => new DataObject(['bar' => ['baz' => "foo\nbar"]])],
                "RAB<br />\nOOF"
            ],
            [
                'foo.getBar().baz|foofilter:myparam|nl2br|doesntexist|nl2br',
                ['foo' => new DataObject(['bar' => ['baz' => "foo\nbar"]])],
                "MARAPYMRAB<br /><br />\nOOF"
            ],
        ];
    }

    private function createConstruction(VarDirective $directive, string $value): array
    {
        preg_match($directive->getRegularExpression(), $value, $construction);

        return $construction;
    }
}
