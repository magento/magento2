<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Class Cdata
 */
class Cdata implements CdataInterface
{
    /**
     * Compiles the CData Section node
     *
     * @param \DOMCdataSection $node
     * @param DataObject $processedObject
     * @return void
     */
    public function compile(\DOMCdataSection $node, DataObject $processedObject)
    {
        //
    }
}
