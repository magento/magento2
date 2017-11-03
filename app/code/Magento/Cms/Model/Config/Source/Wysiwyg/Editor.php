<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Config\Source\Wysiwyg;

/**
 * Configuration source model for Wysiwyg toggling
 */
class Editor implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'tinymce', 'label' => __('TinyMCE 4')]
        ];
    }
}
