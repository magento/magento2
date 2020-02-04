<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor;

use Magento\Framework\Filter\DirectiveProcessorInterface;
use Magento\Framework\Filter\Template;

/**
 * Backwards compatibility directive processor for old directives that still extend from Template
 */
class LegacyDirective implements DirectiveProcessorInterface
{
    /**
     * @var SimpleDirective
     */
    private $simpleDirective;

    /**
     * @param SimpleDirective $simpleDirective
     */
    public function __construct(SimpleDirective $simpleDirective)
    {
        $this->simpleDirective = $simpleDirective;
    }

    /**
     * @inheritdoc
     */
    public function process(array $construction, Template $filter, array $templateVariables): string
    {
        try {
            $reflectionClass = new \ReflectionClass($filter);
            $method = $reflectionClass->getMethod($construction[1] . 'Directive');
            $method->setAccessible(true);

            return (string)$method->invokeArgs($filter, [$construction]);
        } catch (\ReflectionException $e) {
            // The legacy parser may be the only parser loaded so make sure the simple directives still process
            preg_match($this->simpleDirective->getRegularExpression(), $construction[0], $simpleConstruction);

            return $this->simpleDirective->process($simpleConstruction, $filter, $templateVariables);
        }
    }

    /**
     * @inheritdoc
     */
    public function getRegularExpression(): string
    {
        return Template::CONSTRUCTION_PATTERN;
    }
}
