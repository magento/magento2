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
     *
     * @return array
     */
    public function afterToOptionArray(\Magento\Cms\Model\Config\Source\Wysiwyg\Editor $subject, $optionArray)
    {
        foreach ($optionArray as $optionIndex => $optionData) {
            if ($optionData['value'] === 'tinymce') {
                $optionArray[$optionIndex]['label'] = 'TinyMCE 3';
            }
        }

        return $optionArray;
    }
}
