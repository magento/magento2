<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ContentTypeInterface
 * @since 2.0.0
 */
interface ContentTypeInterface
{
    /**
     * Render component
     *
     * @param UiComponentInterface $component
     * @param string $template
     * @return string
     * @since 2.0.0
     */
    public function render(UiComponentInterface $component, $template = '');
}
