<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ObserverInterface
 */
interface ObserverInterface
{
    /**
     * Update component according to $component
     *
     * @param UiComponentInterface $component
     * @return void
     */
    public function update(UiComponentInterface $component);
}
