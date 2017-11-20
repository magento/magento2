<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CKEditor\CKEditor4\Model\Config\Source\Wysiwyg;

class EditorPlugin
{
    public function afterToOptionArray(\Magento\Cms\Model\Config\Source\Wysiwyg\Editor $subject, $optionArray)
    {
        $optionArray[] = ['value' => 'CKEditor_CKEditor4/ckeditor4Adapter', 'label' => __('CKEditor 4')];

        return $optionArray;
    }
}
