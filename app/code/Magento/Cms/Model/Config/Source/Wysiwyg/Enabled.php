<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Config\Source\Wysiwyg;

/**
 * Configuration source model for Wysiwyg toggling
 * @since 2.0.0
 */
class Enabled implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\Cms\Model\Wysiwyg\Config::WYSIWYG_ENABLED, 'label' => __('Enabled by Default')],
            ['value' => \Magento\Cms\Model\Wysiwyg\Config::WYSIWYG_HIDDEN, 'label' => __('Disabled by Default')],
            ['value' => \Magento\Cms\Model\Wysiwyg\Config::WYSIWYG_DISABLED, 'label' => __('Disabled Completely')]
        ];
    }
}
