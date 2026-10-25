<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ContentTypeInterface
 *
 * @api
 */
interface ContentTypeInterface
{
    /**
     * Render component
     *
     * @param UiComponentInterface $component
     * @param string $template
     * @return string
     */
    public function render(UiComponentInterface $component, $template = '');
}
