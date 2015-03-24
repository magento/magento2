<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler\Directive;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Variable
 */
class Variable implements DirectiveInterface
{
    /**
     * Execute directive
     *
     * @param array $directive
     * @param UiComponentInterface $component
     * @return string
     */
    public function execute($directive, UiComponentInterface $component)
    {
        return $component->getData($directive[1]);
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
