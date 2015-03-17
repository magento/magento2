<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface TextInterface
 */
interface TextInterface
{
    /**
     * Compiles the Element node
     *
     * @param \DOMText $node
     * @param UiComponentInterface $component
     * @return void
     */
    public function compile(\DOMText $node, UiComponentInterface $component);
}
