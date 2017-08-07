<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Control;

/**
 * Class DummyButton
 * NullObject for disable buttons
 * @since 2.1.0
 */
class DummyButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getButtonData()
    {
        return [];
    }
}
