<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\Object;

/**
 * Interface CdataInterface
 */
interface CdataInterface
{
    /**
     * Compiles the CData Section node
     *
     * @param \DOMCdataSection $node
     * @param Object $processedObject
     * @return void
     */
    public function compile(\DOMCdataSection $node, Object $processedObject);
}
