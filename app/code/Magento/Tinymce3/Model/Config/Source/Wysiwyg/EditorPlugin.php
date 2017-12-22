<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Model\Config\Source\Wysiwyg;

class EditorPlugin
{
    /**
     * Change label of TinyMCE to v3
     *
     * @param \Magento\Cms\Model\Config\Source\Wysiwyg\Editor $subject
     * @param $optionArray
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToOptionArray(\Magento\Cms\Model\Config\Source\Wysiwyg\Editor $subject, $optionArray)
    {
        $optionArray[] = ['value' => Editor::WYSIWYG_EDITOR_CONFIG_VALUE, 'label' => __('TinyMCE 3 (deprecated)')];

        return $optionArray;
    }
}
