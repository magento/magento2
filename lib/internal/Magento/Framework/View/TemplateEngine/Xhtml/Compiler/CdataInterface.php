<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Interface CdataInterface
 *
 * @api
 */
interface CdataInterface
{
    /**
     * Compiles the CData Section node
     *
     * @param \DOMCdataSection $node
     * @param DataObject $processedObject
     * @return void
     */
    public function compile(\DOMCdataSection $node, DataObject $processedObject);
}
