<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler\Directive;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface DirectiveInterface
 */
interface DirectiveInterface
{
    /**
     * Execute directive
     *
     * @param array $directive
     * @param UiComponentInterface $component
     * @return string
     */
    public function execute($directive, UiComponentInterface $component);

    /**
     * Get regexp search pattern
     *
     * @return string
     */
    public function getPattern();
}
