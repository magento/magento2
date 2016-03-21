<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ContentTypeInterface
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
