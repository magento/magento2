<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Interface CdataInterface
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
