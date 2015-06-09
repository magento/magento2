<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive;

use Magento\Framework\Object;

/**
 * Interface DirectiveInterface
 */
interface DirectiveInterface
{
    /**
     * Execute directive
     *
     * @param array $directive
     * @param Object $processedObject
     * @return string
     */
    public function execute($directive, Object $processedObject);

    /**
     * Get regexp search pattern
     *
     * @return string
     */
    public function getPattern();
}
