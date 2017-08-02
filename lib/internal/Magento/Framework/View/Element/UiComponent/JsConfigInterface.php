<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface JsConfigInterface
 * @since 2.0.0
 */
interface JsConfigInterface extends UiComponentInterface
{
    /**
     * Get configuration of related JavaScript Component
     *
     * @param UiComponentInterface $component
     * @return array
     * @since 2.0.0
     */
    public function getJsConfig(UiComponentInterface $component);
}
