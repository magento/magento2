<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Cdata
 */
class Cdata implements CdataInterface
{
    /**
     * Compiles the CData Section node
     *
     * @param \DOMCdataSection $node
     * @param UiComponentInterface $component
     * @return void
     */
    public function compile(\DOMCdataSection $node, UiComponentInterface $component)
    {
        //
    }
}