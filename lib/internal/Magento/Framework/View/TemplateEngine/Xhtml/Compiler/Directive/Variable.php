<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
