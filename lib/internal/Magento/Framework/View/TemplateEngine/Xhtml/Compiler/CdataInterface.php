<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Interface CdataInterface
 * @since 2.0.0
 */
interface CdataInterface
{
    /**
     * Compiles the CData Section node
     *
     * @param \DOMCdataSection $node
     * @param DataObject $processedObject
     * @return void
     * @since 2.0.0
     */
    public function compile(\DOMCdataSection $node, DataObject $processedObject);
}
