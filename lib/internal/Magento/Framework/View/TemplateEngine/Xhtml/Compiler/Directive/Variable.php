<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive;

use Magento\Framework\Object;

/**
 * Class Variable
 */
class Variable implements DirectiveInterface
{
    /**
     * Execute directive
     *
     * @param array $directive
     * @param Object $processedObject
     * @return string
     */
    public function execute($directive, Object $processedObject)
    {
        return $processedObject->getData($directive[1]);
    }

    /**
     * Get regexp search pattern
     *
     * @return string
     */
    public function getPattern()
    {
        return '#\{\{([^\}\(]+)\}\}#';
    }
}
