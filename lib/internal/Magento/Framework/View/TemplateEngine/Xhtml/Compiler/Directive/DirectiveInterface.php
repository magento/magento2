<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive;

use Magento\Framework\DataObject;

/**
 * Interface DirectiveInterface
 *
 * @api
 */
interface DirectiveInterface
{
    /**
     * Execute directive
     *
     * @param array $directive
     * @param DataObject $processedObject
     * @return string
     */
    public function execute($directive, DataObject $processedObject);

    /**
     * Get regexp search pattern
     *
     * @return string
     */
    public function getPattern();
}
