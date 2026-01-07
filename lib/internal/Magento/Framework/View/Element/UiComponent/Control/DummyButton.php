<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\UiComponent\Control;

/**
 * Class DummyButton
 * NullObject for disable buttons
 */
class DummyButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }
}
