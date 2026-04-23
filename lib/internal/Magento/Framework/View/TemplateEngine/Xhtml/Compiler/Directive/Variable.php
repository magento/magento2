<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive;

use Magento\Framework\DataObject;

/**
 * Class Variable
 */
class Variable implements DirectiveInterface
{
    /**
     * Execute directive
     *
     * @param array $directive
     * @param DataObject $processedObject
     * @return string
     */
    public function execute($directive, DataObject $processedObject)
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
