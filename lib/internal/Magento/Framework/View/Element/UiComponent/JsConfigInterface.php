<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface JsConfigInterface
 */
interface JsConfigInterface extends UiComponentInterface
{
    /**
     * Get JS config
     *
     * @return array
     */
    public function getJsConfig();
}
