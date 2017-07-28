<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive;

use Magento\Framework\DataObject;

/**
 * Interface DirectiveInterface
 * @since 2.0.0
 */
interface DirectiveInterface
{
    /**
     * Execute directive
     *
     * @param array $directive
     * @param DataObject $processedObject
     * @return string
     * @since 2.0.0
     */
    public function execute($directive, DataObject $processedObject);

    /**
     * Get regexp search pattern
     *
     * @return string
     * @since 2.0.0
     */
    public function getPattern();
}
