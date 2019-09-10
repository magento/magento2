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
     * @inheritdoc
     */
    public function process(array $construction, Template $template, array $templateVariables): string
    {
        try {
            $reflectionClass = new \ReflectionClass($template);
            $method = $reflectionClass->getMethod($construction[1] . 'Directive');
            $method->setAccessible(true);

            return (string)$method->invokeArgs($template, [$construction]);
        } catch (\ReflectionException $e) {
            return $construction[0];
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
