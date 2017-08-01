<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Control;

/**
 * Interface ButtonProviderInterface
 * @since 2.0.0
 */
interface ButtonProviderInterface
{
    /**
     * Retrieve button-specified settings
     *
     * @return array
     * @since 2.0.0
     */
    public function getButtonData();
}
