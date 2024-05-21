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
use PHPUnit\Framework\TestCase;

class TemplateDirectiveTest extends TestCase
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
        $this->filter = $objectManager->create(Template::class);
        $this->processor = $objectManager->create(TemplateDirective::class);
    }

    public function testNoTemplateProcessor()
    {
        $template = 'blah {{template config_path="foo"}} blah';
        $result = $this->processor->process($this->createConstruction($this->processor, $template), $this->filter, []);
        self::assertEquals('{Error in template processing}', $result);
    }

    public function testNoConfigPath()
    {
        $this->filter->setTemplateProcessor([$this, 'processTemplate']);
        $template = 'blah {{template}} blah';
        $result = $this->processor->process($this->createConstruction($this->processor, $template), $this->filter, []);
        self::assertEquals('{Error in template processing}', $result);
    }

    /**
     * @dataProvider useCaseProvider
     */
    public function testCases(string $template, array $variables, string $expect)
    {
        $this->filter->setTemplateProcessor([$this, 'processTemplate']);
        $result = $this->processor->process(
            $this->createConstruction($this->processor, $template),
            $this->filter,
            $variables
        );
        self::assertEquals($expect, $result);
    }

    public static function useCaseProvider()
    {
        $prefix = '{{template config_path=$path param1=myparam ';
        $expect = 'path=varpath/myparamabc/varpath';

        return [
            [$prefix . 'varparam=$foo}}',['foo' => 'abc','path'=>'varpath'], $expect],
            [$prefix . 'varparam=$foo.bar}}',['foo' => ['bar' => 'abc'],'path'=>'varpath'], $expect],
            [
                $prefix . 'varparam=$foo.getBar().baz}}',
                ['foo' => new DataObject(['bar' => ['baz' => 'abc']]),'path'=>'varpath'],
                $expect
            ],
        ];
    }

    public function processTemplate(string $configPath, array $parameters)
    {
        // Argument
        return 'path=' . $configPath
            // Directive argument
            . '/' . $parameters['param1'] . $parameters['varparam']
            // Template variable
            . '/' . $parameters['path'];
    }

    private function createConstruction(TemplateDirective $directive, string $value): array
    {
        preg_match($directive->getRegularExpression(), $value, $construction);

        return $construction;
    }
}
