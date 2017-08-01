<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Interface for Template Engine
 * @since 2.0.0
 */
interface TemplateEngineInterface
{
    /**
     * Render template
     *
     * Render the named template in the context of a particular block and with
     * the data provided in $vars.
     *
     * @param \Magento\Framework\View\Element\BlockInterface $block
     * @param string $templateFile
     * @param array $dictionary
     * @return string rendered template
     * @since 2.0.0
     */
    public function render(
        \Magento\Framework\View\Element\BlockInterface $block,
        $templateFile,
        array $dictionary = []
    );
}
