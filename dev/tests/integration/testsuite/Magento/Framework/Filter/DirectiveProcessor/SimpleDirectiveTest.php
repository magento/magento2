<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filter\DirectiveProcessor\Filter\FilterPool;
use Magento\Framework\Filter\DirectiveProcessor\Filter\NewlineToBreakFilter;
use Magento\Framework\Filter\SimpleDirective\ProcessorPool;
use Magento\Framework\Filter\Template;
use Magento\TestModuleSimpleTemplateDirective\Model\FooFilter;
use Magento\TestModuleSimpleTemplateDirective\Model\MyDirProcessor;
use PHPUnit\Framework\TestCase;

class SimpleDirectiveTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    public function testFallbackWhenDirectiveNotFound()
    {
        $filter = $this->objectManager->get(Template::class);
        $processor = $this->createWithProcessorsAndFilters([], []);

        $template = 'blah {{foo bar}} blah';
        $result = $processor->process($this->createConstruction($processor, $template), $filter, []);
        self::assertEquals('{{foo bar}}', $result);
    }

    public function testProcessorAndFilterPoolsAreUsed()
    {
        $filter = $this->objectManager->create(Template::class);

        $processor = $this->createWithProcessorsAndFilters(
            ['mydir' => $this->objectManager->create(MyDirProcessor::class)],
            [
                'foofilter' => $this->objectManager->create(FooFilter::class),
                'nl2br' => $this->objectManager->create(NewlineToBreakFilter::class)
            ]
        );

        $template = 'blah {{mydir "somevalue" param1=yes|foofilter|nl2br|doesntexist|foofilter}}blah '
            . "\n" . '{{var address}} blah{{/mydir}} blah';
        $result = $processor->process($this->createConstruction($processor, $template), $filter, ['foo' => 'foobar']);
        self::assertEquals('SOMEVALUEYESBLAH ' . "\n" .'>/ RB< BLAH', $result);
    }

    public function testDefaultFiltersAreUsed()
    {
        $filter = $this->objectManager->create(Template::class);

        $processor = $this->createWithProcessorsAndFilters(
            ['mydir' => $this->objectManager->create(MyDirProcessor::class)],
            ['foofilter' => $this->objectManager->create(FooFilter::class)]
        );

        $template = 'blah {{mydir "somevalue" param1=yes}}blah '
            . "\n" . '{{var address}} blah{{/mydir}} blah';
        $result = $processor->process($this->createConstruction($processor, $template), $filter, []);
        self::assertEquals('HALB ' . "\n" . ' HALBSEYEULAVEMOS', $result);
    }

    public function testParametersAreParsed()
    {
        $filter = $this->objectManager->create(Template::class);
        $filter->setStrictMode(false);

        $processor = $this->createWithProcessorsAndFilters(
            ['mydir' => $this->objectManager->create(MyDirProcessor::class)],
            ['foofilter' => $this->objectManager->create(FooFilter::class)]
        );

        $template = '{{mydir "somevalue" param1=$bar}}blah{{/mydir}}';
        $result = $processor->process($this->createConstruction($processor, $template), $filter, ['bar' => 'abc']);
        self::assertEquals('HALBCBAEULAVEMOS', $result);
    }

    private function createWithProcessorsAndFilters(array $processors, array $filters): SimpleDirective
    {
        return $this->objectManager->create(
            SimpleDirective::class,
            [
                'processorPool' => $this->objectManager->create(ProcessorPool::class, ['processors' => $processors]),
                'filterPool' => $this->objectManager->create(FilterPool::class, ['filters' => $filters]),
            ]
        );
    }

    private function createConstruction(SimpleDirective $directive, string $value): array
    {
        preg_match($directive->getRegularExpression(), $value, $construction);

        return $construction;
    }
}
